<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordPolicyRule implements Rule
{
    private $validationMsg;

    public function passes($attribute, $value)
    {
        $this->validationMsg = trans('validation.required', ['attribute' => 'password']);

        if (strlen($value) < 12) {
            $this->validationMsg = trans('validation.min.string', ['attribute' => 'password', 'min' => 12]);

            return false;
        }

        $failedTimes = 0;

        // Lower case test
        if (! preg_match('/(\p{Ll}+.*)/u', $value)) {
            $this->validationMsg = trans('validation.passwords_validator.mixed_cases_lowercase', ['attribute' => 'password']);

            $failedTimes++;
        }

        // Upper case test
        if (! preg_match('/(\p{Lu}+.*)/u', $value)) {
            $this->validationMsg = trans('validation.passwords_validator.mixed_cases_uppercase', ['attribute' => 'password']);

            $failedTimes++;
        }

        // numbers test
        if (! preg_match('/\pN/u', $value)) {
            $this->validationMsg = trans('validation.passwords_validator.numbers', ['attribute' => 'password']);

            $failedTimes++;
        }

        // extra symbols
        if (! preg_match('/\p{Z}|\p{S}|\p{P}/u', $value)) {
            $this->validationMsg = trans('validation.passwords_validator.symbols', ['attribute' => 'password']);

            $failedTimes++;
        }

        return $failedTimes <= 1;
    }

    public function message()
    {
        return $this->validationMsg;
    }
}
