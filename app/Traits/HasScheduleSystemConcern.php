<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Arr;

trait HasScheduleSystemConcern
{
    public function getMinMaxTimeProperty()
    {
        if (! $this->schedule->date) {
            $this->schedule->date = decrypt($this->encryptedDate);
        }

        $openingTimes = $this->currentChild->organization->settings['opening_times'];

        $currentWeekDay = Carbon::parse($this->schedule->date)->dayOfWeek - 1;

        $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

        $min = '00:00:00';
        $max = '23:30:00';

        if ($openingTime) {
            $min = $openingTime['start'];
            $max = $openingTime['end'];
        }

        $contract = $this->currentChild->contract;

        if ($contract->bring_until) {
            $min = $contract->bring_until;
        }

        if ($contract->collect_until) {
            $max = $contract->collect_until;
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    public function getEatsOnsiteOrgDefaultsProperty()
    {
        $user = $this->currentChild;
        $org = $user->organization;

        $organizationSettings = optional($org)->settings;
        if (! $organizationSettings) {
            $organizationSettings = [];
        }

        return $this->getValueByKey($organizationSettings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
    }

    public function getLeadTimeProperty()
    {
        $limitations = $this->currentChild->organization->settings['limitations'];

        return $limitations['lead_time'];
    }

    public function getSelectionTimeProperty()
    {
        $limitations = $this->currentChild->organization->settings['limitations'];

        return $limitations['selection_time'];
    }

    public function getUserAvailabilityProperty()
    {
        $orgSettings = $this->currentChild->organization->settings;
        $userSettings = [];

        if ($this->currentChild->isUser()) {
            $userSettings = $this->currentChild->settings;
        }

        if (! $userSettings) {
            $userSettings = [];
        }

        $availability = $orgSettings['availability'];
        if (Arr::has($userSettings, 'availability')) {
            $availability = $userSettings['availability'];
        }

        return $availability;
    }

    public function isMealTypeDisabled($mealType)
    {
        return ! $this->eatsOnsiteOrgDefaults[$mealType];
    }

    public function isCurrentlyCheckedOut()
    {
        return (bool) $this->schedule->check_out;
    }

    public function isScheduleDisabled()
    {
        return $this->scheduleOrMealStateCheck();
    }

    public function isMealUpdatesLocked()
    {
        return $this->scheduleOrMealStateCheck(true);
    }

    protected function scheduleOrMealStateCheck($isForMeal = false, $schedule = null)
    {
        $settings = $this->currentChild->organization->settings;
        $leadTime = (int) $settings['limitations']['lead_time'];

        $lockTime = $this->minMaxTime['min'];
        if ($isForMeal) {
            if (Arr::has($settings, 'food_lock_time')) {
                $lockTime = $settings['food_lock_time'];
            }
        } else {
            if (Arr::has($settings, 'schedule_lock_time')) {
                $lockTime = $settings['schedule_lock_time'];
            }
        }

        $date = Carbon::parse($this->schedule->date);

        if ($schedule) {
            $date = Carbon::parse($schedule->date);
        }

        if (now() < $date->copy()->setTimeFromTimeString($lockTime)) {
            return false;
        }

        return true;
    }

    public function getMealNeedsApprovalProperty()
    {
        return $this->scheduleOrMealNeedsApproval(true);
    }

    public function getScheduleNeedsApprovalProperty()
    {
        return $this->scheduleOrMealNeedsApproval(false);
    }

    protected function scheduleOrMealNeedsApproval($isForMeal = false)
    {
        $settings = $this->currentChild->organization->settings;
        $leadTime = (int) $settings['limitations']['lead_time'];

        $lockTime = $this->minMaxTime['min'];
        if ($isForMeal) {
            if (Arr::has($settings, 'food_lock_time')) {
                $lockTime = $settings['food_lock_time'];
            }
        } else {
            if (Arr::has($settings, 'schedule_lock_time')) {
                $lockTime = $settings['schedule_lock_time'];
            }
        }

        // $date = Carbon::parse($this->schedule->date)->setTimeFromTimeString($lockTime);

        // $diff = now()->startOfDay()->diffInDays($date->copy()->endOfDay());

        // if ($leadTime == 0 && $diff == $leadTime) {
        //     $dateWithLimit = $date->copy()->setTimeFromTimeString($lockTime);

        //     if (now()->gt($dateWithLimit)) {
        //         return true;
        //     }

        //     return false;
        // }

        // $dateWithLimit = $date->copy()->subDays(1)->setTimeFromTimeString('23:59:59');

        // return now()->gt($dateWithLimit);

        $date = now()->setTimeFromTimeString($lockTime);

        if ($leadTime > 0) {
            $date = now()->subDays($leadTime)->endOfDay();
        }

        $scheduleDate = Carbon::parse($this->schedule->date);

        if ($scheduleDate->isSameDay(now()) && ! $isForMeal) {
            return true;
        }

        if ($scheduleDate > $date) {
            return false;
        }

        return true;
    }

    protected function needsTimeSchedule()
    {
        return $this->userAvailability === 'not-available-with-time';
    }

    public function getCanAutoApproveProperty()
    {
        $settings = $this->currentChild->organization->settings;

        if (Arr::has($settings, 'schedule_auto_approve') && $settings['schedule_auto_approve']) {
            return true;
        }

        return false;
    }
}
