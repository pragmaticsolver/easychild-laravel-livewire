<?php

namespace App\Http\Livewire\Schedules;

use App\CustomNotification\DatabaseNotificationModel;
use App\Http\Livewire\Component;
use App\Models\CustomJob;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\AttendanceNotification;
use App\Notifications\UserAttendanceDealedNotification;
use App\Traits\HasScheduleNotificationConcern;
use App\Traits\HasScheduleSystemConcern;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class CreateItem extends Component
{
    use HasScheduleSystemConcern, HasScheduleNotificationConcern;

    public Schedule $schedule;
    public User $currentChild;
    public $currentApproved;

    public $encryptedDate;

    public $min = '00:00';
    public $max = '23:30';

    public $isFirstInLoop = false;

    public function mount(Schedule $schedule, $currentChild, $firstInLoop = false)
    {
        $this->currentChild = $currentChild;
        $this->schedule = $schedule;

        $this->isFirstInLoop = $firstInLoop;

        $this->setCurrentApproved();

        $this->encryptedDate = encrypt($this->schedule->date);
    }

    protected function setCurrentApproved()
    {
        if ($this->schedule->status == 'approved') {
            $this->currentApproved = encrypt([
                'start' => $this->schedule->start != 'XX:XX' ? $this->schedule->start : null,
                'end' => $this->schedule->end != 'XX:XX' ? $this->schedule->end : null,
                'eats_onsite' => $this->getEatsOnsiteBasedOnOrg($this->schedule->eats_onsite),
                'available' => $this->schedule->available,
            ]);
        } else {
            $this->currentApproved = encrypt($this->schedule->current_approved);
        }
    }

    public function getDisplayTextProperty()
    {
        $date = Carbon::parse(decrypt($this->encryptedDate));

        $day = $date->day;
        if ($day < 10) {
            $day = '0'.$day;
        }

        return $day.'. '.$date->shortMonthName;
    }

    public function refreshCheckout($data)
    {
        $date = decrypt($this->encryptedDate);
        $schedule = Schedule::where([
            'user_id' => $this->currentChild->id,
            'date' => $date,
        ])->first();

        if ($schedule) {
            $this->schedule = $schedule;
        } else {
            return;
        }

        $updateData = collect($data)->only(['start', 'end', 'available', 'check_out', 'eats_onsite'])->all();

        $this->authorize('update', $this->schedule);

        $this->schedule->update($updateData);

        $this->dispatchBrowserEvent('schedule-update-'.$date, [
            'start' => $this->schedule->start,
            'end' => $this->schedule->end,
            'available' => $this->schedule->available,
            'check_out' => $this->schedule->check_out,
            'eats_onsite' => $this->schedule->eats_onsite,
        ]);
    }

    public function save($data, $fromWhere)
    {
        $date = decrypt($this->encryptedDate);
        if (! $this->schedule->date) {
            $this->schedule->date = $date;
        }

        if (! $this->schedule->user_id) {
            $this->schedule->user_id = $this->currentChild->id;
        }

        $this->schedule->available = $data['available'];
        $this->schedule->eats_onsite = $data['eats_onsite'];
        $this->schedule->check_out = $data['check_out'];

        if ($this->needsTimeSchedule()) {
            $this->schedule->start = $data['start'];
            $this->schedule->end = $data['end'];

            $this->setDefaultSchedule();
        } else {
            $this->schedule->start = null;
            $this->schedule->end = null;
        }

        $rules = [
            'schedule.available' => 'required|boolean',
            'schedule.check_out' => '',
            'schedule.eats_onsite.breakfast' => 'required|boolean',
            'schedule.eats_onsite.lunch' => 'required|boolean',
            'schedule.eats_onsite.dinner' => 'required|boolean',
        ];

        if ($this->needsTimeSchedule()) {
            $rules['schedule.start'] = 'required';
            $rules['schedule.end'] = 'required';
        }

        $this->validate($rules);

        $this->saveSchedule($fromWhere);

        $this->dispatchBrowserEvent('schedule-update-'.$date, [
            'start' => $this->schedule->start,
            'status' => $this->schedule->status,
            'end' => $this->schedule->end,
            'available' => $this->schedule->available,
            'check_out' => $this->schedule->check_out,
            'eats_onsite' => $this->schedule->eats_onsite,
        ]);
    }

    private function setDefaultSchedule()
    {
        if (! $this->schedule->available) {
            return;
        }

        if ($this->doesNotHaveTime() || $this->doesNotHaveTime('end')) {
            $schedule = Schedule::query()
                ->where('user_id', $this->currentChild->id)
                ->whereNotNull('start')
                ->whereNotNull('end')
                ->orderBy('date', 'DESC')
                ->first();

            if ($schedule) {
                $this->schedule->start = $schedule->start;
                $this->schedule->end = $schedule->end;
            }
        }

        if ($this->doesNotHaveTime()) {
            $this->schedule->start = $this->minMaxTime['min'];
        }

        if ($this->doesNotHaveTime('end')) {
            $this->schedule->end = $this->minMaxTime['max'];
        }
    }

    private function doesNotHaveTime($type = 'start')
    {
        if ((! $this->schedule->$type) || $this->schedule->$type == 'XX:XX') {
            return true;
        }

        return false;
    }

    private function checkAndRefreshEatsOnsite()
    {
        $this->schedule->eats_onsite = $this->userEatsOnsite;
    }

    public function getUserEatsOnsiteProperty()
    {
        $orgSettings = $this->currentChild->organization->settings;
        $userSettings = $this->currentChild->settings;
        $options = ['breakfast', 'lunch', 'dinner'];

        if (! $userSettings) {
            $userSettings = [];
        }

        $eatsOnsite = $orgSettings['eats_onsite'];
        if (Arr::has($userSettings, 'eats_onsite')) {
            foreach ($options as $option) {
                if (Arr::has($userSettings['eats_onsite'], $option) && gettype($userSettings['eats_onsite'][$option]) == 'boolean') {
                    $eatsOnsite[$option] = $userSettings['eats_onsite'][$option];
                } else {
                    $eatsOnsite[$option] = $orgSettings['eats_onsite'][$option];
                }

                if (! $orgSettings['eats_onsite'][$option]) {
                    $eatsOnsite[$option] = null;
                }
            }
        }

        return $eatsOnsite;
    }

    public function saveSchedule($fromWhere = null)
    {
        if (! $this->schedule->date) {
            $this->schedule->date = decrypt($this->encryptedDate);
        }

        // $this->checkAndRefreshEatsOnsite();

        if ($this->needsTimeSchedule()) {
            if ($this->isScheduleDisabled()) {
                return $this->emitMessage('error', trans('schedules.disabled_schedule'));
            }

            if ($this->dataValidate()) {
                return;
            }
        } else {
            $this->schedule->start = null;
            $this->schedule->end = null;

            if ($fromWhere == 'meal' && $this->isMealUpdatesLocked()) {
                return $this->emitMessage('error', trans('schedules.disabled_schedule'));
            }

            if ($fromWhere == 'available' && $this->isScheduleDisabled()) {
                return $this->emitMessage('error', trans('schedules.disabled_schedule'));
            }
        }

        if ($this->needsTimeSchedule()) {
            $this->schedule->available = true;
        }

        $allergy = null;
        if (Arr::has($this->currentChild->settings, 'allergies')) {
            $allergy = $this->currentChild->settings['allergies'];
        }

        $this->schedule->allergy = $allergy;

        if ($fromWhere == 'available' && $this->schedule->status == 'approved') {
            $this->schedule->eats_onsite = $this->getCurrentApprovedEatsOnsite();
        } else {
            $this->schedule->eats_onsite = $this->getEatsOnsiteBasedOnOrg($this->schedule->eats_onsite);
        }

        if ($this->schedule->getKey()) {
            $alreadyHasSchedule = Schedule::query()
                ->where('user_id', $this->currentChild->id)
                ->where('date', decrypt($this->encryptedDate))
                ->first();

            if ($alreadyHasSchedule) {
                if ($alreadyHasSchedule->uuid != $this->schedule->uuid) {
                    $this->authorize('update', $alreadyHasSchedule);

                    $this->schedule = $alreadyHasSchedule;

                    return $this->emitMessage('error', trans('schedules.duplicate_entry_error'));
                }
            } else {
                return $this->dispatchBrowserEvent('reload-browser');
            }

            $this->authorize('update', $this->schedule);

            $this->emitMessage('success', trans('schedules.update_success', ['day' => $this->schedule->date]));
        } else {
            $alreadyHasSchedule = Schedule::query()
                ->where('user_id', $this->currentChild->id)
                ->where('date', decrypt($this->encryptedDate))
                ->first();

            if ($alreadyHasSchedule) {
                $this->schedule = $alreadyHasSchedule;

                return $this->emitMessage('error', trans('schedules.duplicate_entry_error'));
            } else {
                $this->schedule->user_id = $this->currentChild->id;
            }

            $this->emitMessage('success', trans('schedules.create_success', ['day' => $this->schedule->date]));
        }

        $addCustomJob = false;

        // changing to pending if data is changed
        if (in_array($fromWhere, ['available', 'startOrEnd'])) {
            if (! $this->scheduleNeedsApproval && $this->canAutoApprove) {
                $this->schedule->status = 'approved';
                $this->schedule->current_approved = null;
                $this->schedule->last_dealt_at = now();

                $this->setCurrentApproved();
            } else {
                $this->schedule->last_dealt_at = null;
                $this->schedule->status = 'pending';

                $currentApproved = decrypt($this->currentApproved);

                if (! empty($currentApproved)) {
                    $this->schedule->current_approved = $currentApproved;
                }

                $addCustomJob = true;
            }
        } else {
            // Check if food needs to be approved
            if ($this->schedule->status == 'approved') {
                // $this->schedule->status = 'approved';
                $this->schedule->current_approved = null;
                $this->setCurrentApproved();
            }
        }

        $this->checkCanAutoApproveIfDataMatch();
        $this->schedule->save();

        $addCustomJob && $this->schedule->status == 'pending' && $this->addNotificationToCustomJob($this->schedule);

        if ($this->schedule->status == 'approved') {
            $this->removeRelatedNotifications($this->schedule);
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

    protected function getCurrentApprovedEatsOnsite()
    {
        $currentApproved = $this->schedule->current_approved;

        if (empty($currentApproved)) {
            return [
                'breakfast' => $this->isMealTypeDisabled('breakfast') ? null : true,
                'lunch' => $this->isMealTypeDisabled('lunch') ? null : true,
                'dinner' => $this->isMealTypeDisabled('dinner') ? null : true,
            ];
        }

        return $currentApproved['eats_onsite'];
    }

    protected function checkCanAutoApproveIfDataMatch()
    {
        $currentApproved = $this->schedule->current_approved;

        if (empty($currentApproved)) {
            return;
        }

        $valueChanged = false;

        if ($currentApproved['start'] != $this->schedule->start) {
            $valueChanged = true;
        }

        if ($currentApproved['end'] != $this->schedule->end) {
            $valueChanged = true;
        }

        if ($currentApproved['available'] != $this->schedule->available) {
            $valueChanged = true;
        }

        if ($currentApproved['eats_onsite']['breakfast'] != $this->schedule->eats_onsite['breakfast']) {
            $valueChanged = true;
        }

        if ($currentApproved['eats_onsite']['lunch'] != $this->schedule->eats_onsite['lunch']) {
            $valueChanged = true;
        }

        if ($currentApproved['eats_onsite']['dinner'] != $this->schedule->eats_onsite['dinner']) {
            $valueChanged = true;
        }

        if ($valueChanged) {
            return;
        }

        $this->schedule->status = 'approved';
    }

    public function removeCheckOut($scheduleUuid)
    {
        if ($this->isScheduleDisabled()) {
            return;
        }

        $alreadyHasSchedule = Schedule::query()
            ->where('user_id', $this->currentChild->id)
            ->where('date', decrypt($this->encryptedDate))
            ->first();

        if (! $alreadyHasSchedule) {
            return;
        }

        if ($scheduleUuid && $scheduleUuid != $alreadyHasSchedule->uuid) {
            return;
        }

        $this->schedule = $alreadyHasSchedule;

        $start = null;
        $end = null;

        if ($this->needsTimeSchedule()) {
            $latestScheduleWithTime = $this->currentChild->schedules()
                ->whereNotNull('start')
                ->whereNotNull('end')
                ->orderBy('date', 'DESC')
                ->first();

            if ($latestScheduleWithTime) {
                $start = $latestScheduleWithTime->start;
                $end = $latestScheduleWithTime->end;
            }
        }

        $this->schedule->update([
            'start' => $start,
            'end' => $end,
            'check_out' => null,
            'status' => 'pending',
        ]);

        $this->emitMessage('success', trans('schedules.check_out.confirmation.success'));
    }

    public function removeSchedule($scheduleUuid)
    {
        if ($this->isScheduleDisabled()) {
            return;
        }

        if (! $this->schedule->getKey()) {
            return;
        }

        if ($scheduleUuid != $this->schedule->uuid) {
            return;
        }

        $data = [
            'date' => $this->schedule->date,
            'start' => 'XX:XX',
            'end' => 'XX:XX',
            'eats_onsite' => [
                'breakfast' => false,
                'lunch' => false,
                'dinner' => false,
            ],
            'available' => false,
        ];

        CustomJob::query()
            ->where('related_type', Schedule::class)
            ->where('related_id', $this->schedule->id)
            ->whereIn('action', [
                UserAttendanceDealedNotification::class,
                AttendanceNotification::class,
            ])
            ->delete();

        DatabaseNotificationModel::query()
            ->whereIn('type', [
                UserAttendanceDealedNotification::class,
                AttendanceNotification::class,
            ])
            ->whereHasMorph('related', Schedule::class)
            ->where('related_id', $this->schedule->id)
            ->delete();

        $this->schedule->delete();
        $this->schedule = Schedule::make($data);

        // $this->recentlyDeleted = true;

        $this->emitMessage('success', trans('schedules.remove_success', ['day' => $this->schedule->date]));
    }

    private function dataValidate()
    {
        if (! $this->schedule->start || $this->schedule->start == 'XX:XX') {
            $this->emitMessage('error', trans('schedules.start_required'));

            return true;
        }

        if (! $this->schedule->end || $this->schedule->end == 'XX:XX') {
            $this->emitMessage('error', trans('schedules.end_required'));

            return true;
        }

        $start = Carbon::parse($this->schedule->date)->setTimeFromTimeString($this->schedule->start);
        $end = Carbon::parse($this->schedule->date)->setTimeFromTimeString($this->schedule->end);

        if ($start >= $end) {
            $this->emitMessage('error', trans('schedules.start_earlier_than_end'));

            return true;
        }

        $minTime = Carbon::parse($this->schedule->date.' '.$this->minMaxTime['min']);
        $maxTime = Carbon::parse($this->schedule->date.' '.$this->minMaxTime['max']);

        if ($start < $minTime || $start > $maxTime) {
            $this->emitMessage('error', trans('schedules.start_min_max_limit', [
                'min' => $this->minMaxTime['min'],
                'max' => $this->minMaxTime['max'],
            ]));

            return true;
        }

        if ($end < $minTime || $end > $maxTime) {
            $this->emitMessage('error', trans('schedules.end_min_max_limit', [
                'min' => $this->minMaxTime['min'],
                'max' => $this->minMaxTime['max'],
            ]));

            return true;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.schedules.create-item');
    }
}
