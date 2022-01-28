<?php

namespace App\Rules;

use App\Models\Group;
use Illuminate\Contracts\Validation\Rule;

class OrganizationGroupsRule implements Rule
{
    private $org = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($org = null)
    {
        $this->org = $org;

        if ((! $org) && auth()->user() && auth()->user()->organization_id) {
            $this->org = auth()->user()->organization_id;
        }
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
        if ($value) {
            $group = Group::find($value);

            $user = auth()->user();

            if (! $user->isAdmin() && $this->org != $user->organization_id) {
                return false;
            }

            if ($this->org) {
                if (is_array($value)) {
                    foreach ($group as $groupItem) {
                        if ($groupItem) {
                            if ($groupItem->organization_id != $this->org) {
                                return false;
                            }
                        }
                    }
                } else {
                    if ($group) {
                        if ($group->organization_id != $this->org) {
                            return false;
                        }
                    }
                }
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
        return trans('validation.exists', [
            'attribute' => trans('groups.title_singular'),
        ]);
    }
}
