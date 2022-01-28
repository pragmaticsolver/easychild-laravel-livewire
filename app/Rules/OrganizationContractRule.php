<?php

namespace App\Rules;

use App\Models\Contract;
use Illuminate\Contracts\Validation\Rule;

class OrganizationContractRule implements Rule
{
    public $org;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($org = null)
    {
        $this->org = $org;
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
            $contract = Contract::find($value);

            if ($this->org && $contract) {
                if ($contract->organization_id != $this->org) {
                    return false;
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
            'attribute' => trans('contracts.title_lower'),
        ]);
    }
}
