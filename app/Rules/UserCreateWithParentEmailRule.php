<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class UserCreateWithParentEmailRule implements Rule
{
    public $role;

    public function __construct($role)
    {
        $this->role = $role;
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
        if ($this->role != 'User') {
            return true;
        }

        if (! $value) {
            return true;
        }

        $user = User::query()
            ->where('email', $value)
            ->first();

        if ($user) {
            if ($user->role != 'Parent') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('users.parent.validations.parent_child_link_email_present_with_other_role');
    }
}
