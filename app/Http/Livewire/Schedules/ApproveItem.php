<?php

namespace App\Http\Livewire\Schedules;

use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\UserEventsNotification;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ApproveItem extends Component
{
    public $uuid;
    public User $user;
    public $date;
    public $start;
    public $end;
    public $status;
    public $eatsOnsite;

    public $groupName;
    public $available;

    public $originalData;

    public $min = '00:00';
    public $max = '23:30';

    public function mount($schedule, $openingTimes)
    {
        $this->originalData = $schedule;

        $this->date = $schedule['date'];
        $this->uuid = $this->getValueByKey($schedule, 'uuid', null);
        $this->available = $this->getValueByKey($schedule, 'available', false);
        $this->user = $schedule['user'];

        $this->start = $this->getValueByKey($schedule, 'start', null);
        $this->end = $this->getValueByKey($schedule, 'end', null);

        $this->status = $schedule['status'];

        $this->groupName = $schedule['group_name'];

        $this->eatsOnsite = $schedule['eats_onsite'];

        $currentWeekDay = Carbon::parse($this->date)->dayOfWeek - 1;
        $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

        if ($openingTime) {
            $this->min = $openingTime['start'];
            $this->max = $openingTime['end'];
        }
    }

    private function getUserEatsOnsite($eatsOnsite)
    {
        $options = ['breakfast', 'lunch', 'dinner'];

        foreach ($options as $option) {
            if ($this->isMealTypeDisabled($option)) {
                $eatsOnsite[$option] = null;
            }
        }

        return $eatsOnsite;
    }

    public function getNeedTimeScheduleProperty()
    {
        return $this->userAvailability === 'not-available-with-time';
    }

    public function getEatsOnsiteOrgDefaultsProperty()
    {
        $org = auth()->user()->organization;

        $organizationSettings = optional($org)->settings;
        if (! $organizationSettings) {
            $organizationSettings = [];
        }

        return $this->getValueByKey($organizationSettings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
    }

    public function isMealTypeDisabled($mealType)
    {
        return ! $this->eatsOnsiteOrgDefaults[$mealType];
    }

    public function getUserAvailabilityProperty()
    {
        $orgSettings = auth()->user()->organization->settings;
        $userSettings = $this->user->settings;

        if (! $userSettings) {
            $userSettings = [];
        }

        $availability = $orgSettings['availability'];
        if (Arr::has($userSettings, 'availability')) {
            $availability = $userSettings['availability'];
        }

        return $availability;
    }

    private function dateValidate()
    {
        $start = $this->start;
        $end = $this->end;

        if (! $start || $start == 'XX:XX') {
            $this->emitMessage('error', trans('schedules.start_required'));

            return true;
        }

        if (! $end || $end == 'XX:XX') {
            $this->emitMessage('error', trans('schedules.end_required'));

            return true;
        }

        $startParsed = Carbon::parse($this->date . ' ' . $start);
        $endParsed = Carbon::parse($this->date . ' ' . $end);

        if ($startParsed >= $endParsed) {
            $this->emitMessage('error', trans('schedules.start_earlier_than_end'));

            return true;
        }

        $minTime = Carbon::parse($this->date . ' ' . $this->min);
        $maxTime = Carbon::parse($this->date . ' ' . $this->max);

        if ($startParsed < $minTime || $startParsed > $maxTime) {
            $this->emitMessage('error', trans('schedules.start_min_max_limit', [
                'min' => $this->min,
                'max' => $this->max,
            ]));

            return true;
        }

        if ($endParsed < $minTime || $endParsed > $maxTime) {
            $this->emitMessage('error', trans('schedules.end_min_max_limit', [
                'min' => $this->min,
                'max' => $this->max,
            ]));

            return true;
        }

        if ($this->disabled) {
            $this->emitMessage('error', trans('schedules.disabled_schedule'));

            return true;
        }

        return false;
    }

    public function getDisabledProperty()
    {
        $date = Carbon::parse($this->date . ' ' . $this->min);

        if ($date <= now()->startOfDay() && $date->diffInDays() > 60) {
            return true;
        }

        return false;
    }

    private function removeSecondsFromTime($time)
    {
        if ($time && Str::length($time) === 8) {
            return (string) Str::of($time)->replaceLast(':00', '');
        }

        return $time;
    }

    private function isScheduleDataChanged($schedule)
    {
        if ($this->needTimeSchedule) {
            if ($schedule->start != $this->originalData['start']) {
                return true;
            }

            if ($schedule->end != $this->originalData['end']) {
                return true;
            }
        }

        if ($schedule->status != $this->originalData['status']) {
            return true;
        }

        if ($schedule->eats_onsite != $this->originalData['eats_onsite']) {
            return true;
        }

        if ($schedule->available != $this->originalData['available']) {
            return true;
        }

        return false;
    }

    public function updated($name, $value)
    {
        if ($this->disabled) {
            return;
        }

        if (in_array($name, ['start', 'end'])) {
            if ($this->timeCheckPasses()) {
                $this->saveSchedule('time');
            }
        }

        if (Str::startsWith($name, 'eatsOnsite.')) {
            $this->saveSchedule('eatsOnsite');
        }

        if ($name == 'available') {
            $this->saveSchedule('available');
        }

        if ($name == 'status') {
            $possibleStatus = ['approved', 'declined'];

            if (in_array($this->status, $possibleStatus)) {
                $this->saveSchedule('status');
            } else {
                $this->emitMessage('error', trans('schedules.invalid_status'));
            }
        }
    }

    private function timeCheckPasses()
    {
        if ($this->needTimeSchedule && $this->start && $this->end) {
            if (! $this->dateValidate()) {
                return true;
            }
        }

        return false;
    }

    private function saveSchedule($type)
    {
        $data = [
            'user_id' => $this->user->id,
            'date' => $this->date,
            'available' => $this->available,
            'status' => $this->status,
            'eats_onsite' => $this->getUserEatsOnsite($this->eatsOnsite),
        ];

        if ($type == 'time') {
            $data['start'] = $this->start;
            $data['end'] = $this->end;
            $data['available'] = true;
            $this->available = true;
        }

        if ($data['available']) {
            $data['current_approved'] = null;
        }

        if ($this->uuid) {
            $schedule = Schedule::findByUUIDOrFail($this->uuid);
            $this->authorize('update', $schedule);

            $schedule->update($data);
        } else {
            $schedule = Schedule::updateOrCreate(
                [
                    'user_id' => $this->user->id,
                    'date' => $this->date,
                ],
                collect($data)->except(['user_id', 'date'])->all()
            );
        }

        $schedule->sendDealtNotificationToUser($schedule->status);

        // $this->setCurrentApproved($schedule);
        $this->originalData = $schedule->toArray();

        $this->emitMessage('success', trans('schedules.update_success', ['day' => $this->date]));
    }

    protected function setCurrentApproved($schedule)
    {
        if ($schedule->status == 'approved') {
            $schedule->update([
                'current_approved' => [
                    'start' => $schedule->start != 'XX:XX' ? $schedule->start : null,
                    'end' => $schedule->end != 'XX:XX' ? $schedule->end : null,
                    'eats_onsite' => $this->getEatsOnsiteBasedOnOrg($schedule->eats_onsite),
                    'available' => $schedule->available,
                ],
            ]);
        }
    }

    protected function getEatsOnsiteBasedOnOrg($eatsOnsiteData)
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner'];
        foreach ($mealTypes as $mealItem) {
            if ($this->isMealTypeDisabled($mealItem)) {
                $eatsOnsiteData[$mealItem] = null;
            }
        }

        return $eatsOnsiteData;
    }

    private function BKsaveSchedule()
    {
        $data = [
            'start' => $this->needTimeSchedule ? $this->start : null,
            'end' => $this->needTimeSchedule ? $this->end : null,
            'status' => $this->needTimeSchedule ? $this->status : 'approved',
            'available' => $this->available,
            'eats_onsite' => $this->eatsOnsite,
        ];

        if ($this->uuid) {
            $schedule = Schedule::findByUUIDOrFail($this->uuid);
            $this->authorize('update', $schedule);

            $schedule->update($data);
        } else {
            $data['user_id'] = $this->user->id;
            $data['date'] = $this->date;

            $schedule = Schedule::updateOrCreate(
                [
                    'user_id' => $this->user->id,
                    'date' => $this->date,
                ],
                collect($data)->except(['user_id', 'date'])->all()
            );
        }

        if ($this->isScheduleDataChanged($schedule)) {
            $title = trans('subscription.schedule_updated_title');
            $body = trans('subscription.schedule_updated_user', [
                'date' => $this->date,
                'user' => auth()->user()->full_name,
            ]);
            $url = route('schedules.index');

            // $schedule->user->notify(new UserEventsNotification($title, $body, $url, 'schedule-change'));
        }

        $this->emitMessage('success', trans('schedules.update_success', ['day' => $this->date]));
    }

    public function render()
    {
        return view('livewire.schedules.approve-item');
    }
}
