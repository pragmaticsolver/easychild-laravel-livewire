<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class BetweenOpeningTimeRangeRule implements Rule
{
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
            $bringUntilTime = Carbon::parse($value);
            $minTime = Carbon::parse($this->minMaxProperty()['min']);
            $maxTime = Carbon::parse($this->minMaxProperty()['max']);

            if ($bringUntilTime < $minTime || $bringUntilTime > $maxTime) {
                return false;
            }
        }

        return true;
    }

    protected function minMaxProperty()
    {
        $day = now();

        while ($day->isWeekend()) {
            $day->addDay();
        }

        $openingTimes = auth()->user()->organization->settings['opening_times'];

        $currentWeekDay = $day->dayOfWeek - 1;

        $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

        $min = '00:00';
        $max = '23:30';

        if ($openingTime) {
            $min = $openingTime['start'];
            $max = $openingTime['end'];
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('contracts.bring_collect_error');
    }
}
