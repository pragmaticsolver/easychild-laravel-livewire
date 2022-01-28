<?php

namespace App\Traits;

use App\Events\ScheduleUpdated;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Response;

trait HasSchedulePresenceEditor
{
    public $showEditScheduleForm = false;
    public Schedule $scheduleInEdit;

    public $startTime = null;
    public $presenceStartView = 'XX:XX';

    public $endTime = null;
    public $presenceEndView = 'XX:XX';

    public $minStartTime = null;
    public $maxEndTime = null;

    private function isPreviousDaySchedule($schedule)
    {
        $date = Carbon::parse($schedule->date);

        if ($date > now()->endOfDay()) {
            return false;
        }

        if ($date < now()->subWeek()->startOfDay()) {
            return false;
        }

        return true;
    }

    public function editSchedulePresenceTime($scheduleUuid)
    {
        $schedule = Schedule::findByUUIDOrFail($scheduleUuid);

        $this->presenceStartView = 'XX:XX';
        $this->presenceEndView = 'XX:XX';

        if (auth()->user()->cannot('update', $schedule)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if (! $this->isPreviousDaySchedule($schedule)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if ($schedule->date) {
            $this->scheduleInEdit = $schedule;
        }
        $this->showEditScheduleForm = true;

        if ($schedule->presence_start) {
            $startDateObj = now()->setTimeFromTimeString($schedule->presence_start)->roundMinutes(15);
            $this->startTime = $startDateObj->format('H:i:s');
            $this->presenceStartView = $startDateObj->format('H:i');
        }

        if ($schedule->presence_end) {
            $endDateObj = now()->setTimeFromTimeString($schedule->presence_end)->roundMinutes(15);
            $this->endTime = $endDateObj->format('H:i:s');
            $this->presenceEndView = $endDateObj->format('H:i');
            $this->endTime = now()->setTimeFromTimeString($schedule->presence_end)->format('H:i:s');
        }

        $this->calculateMinAndMaxLimitTime();
    }

    private function calculateMinAndMaxLimitTime()
    {
        $openingTimes = auth()->user()->organization->settings['opening_times'];

        $currentWeekDay = Carbon::parse($this->scheduleInEdit->date)->dayOfWeek - 1;

        $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

        $min = '00:00';
        $max = '23:30';

        if ($openingTime) {
            $min = $openingTime['start'];
            $max = $openingTime['end'];
        }

        $this->minStartTime = $min;
        $this->maxEndTime = $max;
    }

    public function submitScheduleUpdate()
    {
        $this->validate([
            'startTime' => 'required|not_in:XX:XX',
            'endTime' => 'required|not_in:XX:XX',
        ]);

        if (! $this->checkIfHasAuthorization()) {
            return;
        }

        $this->scheduleInEdit->update([
            'presence_start' => $this->startTime,
            'presence_end' => $this->endTime,
        ]);

        $this->updateScheduleLogs();

        $this->startTime = '';
        $this->endTime = '';
        $this->showEditScheduleForm = false;

        $this->emitTo('group-class.item', $this->scheduleInEdit->uuid, $this->scheduleInEdit);

        $this->emitMessage('success', trans('users.log_section.update_success'));
    }

    private function updateScheduleLogs()
    {
        if ($this->scheduleInEdit->wasChanged('presence_start')) {
            $startDateTime = now()->setTimeFromTimeString($this->scheduleInEdit->presence_start);
            ScheduleUpdated::dispatch($this->scheduleInEdit, [
                'type' => 'enter',
                'trigger_type' => 'user-manual',
                'triggred_id' => auth()->id(),
                'datetime' => $startDateTime,
            ]);
        }

        if ($this->scheduleInEdit->wasChanged('presence_end')) {
            $endDateTime = now()->setTimeFromTimeString($this->scheduleInEdit->presence_end);
            ScheduleUpdated::dispatch($this->scheduleInEdit, [
                'type' => 'leave',
                'trigger_type' => 'user-manual',
                'triggred_id' => auth()->id(),
                'datetime' => $endDateTime,
            ]);
        }
    }

    private function checkIfHasAuthorization()
    {
        if (! auth()->user()->isManagerOrPrincipal()) {
            return false;
        }

        if (auth()->user()->cannot('update', $this->scheduleInEdit)) {
            return false;
        }

        if ($this->startTime && $this->endTime) {
            $start = now()->setTimeFromTimeString($this->startTime);
            $end = now()->setTimeFromTimeString($this->endTime);

            if ($start >= $end) {
                $this->addError('startTime', trans('users.log_section.start_time_after_end'));

                return false;
            }
        }

        return true;
    }
}
