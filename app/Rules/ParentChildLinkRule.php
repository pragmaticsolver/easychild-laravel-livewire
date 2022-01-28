<?php

namespace App\Rules;

use App\Models\ParentLink;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ParentChildLinkRule implements Rule
{
    public User $child;
    public $status;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(User $child)
    {
        $this->child = $child;
        $this->status = 'not-linked';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $parentLink = ParentLink::query()
            ->where('email', $value)
            ->where('child_id', $this->child->id)
            ->first();

        if ($parentLink) {
            if ($parentLink->linked) {
                $this->status = 'linked';
            }

            return false;
        }

        return ! ! $parentLink;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return [
            'linked' => trans('parents.error_parent_already_linked'),
            'not-linked' => trans('parents.error_parent_linked_but_not_accepted'),
        ][$this->status];
    }
}
