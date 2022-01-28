<?php

namespace App\Http\Livewire\Schedules;

use App\CustomNotification\DatabaseNotificationModel;
use App\Http\Livewire\Component;
use App\Models\CustomJob;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\AttendanceNotification;
use App\Notifications\UserAttendanceDealedNotification;
use App\Traits\HasScheduleSystemConcern;
use Carbon\Carbon;

class CreateItemCopy extends Component
{
    use HasScheduleSystemConcern;

    public Schedule $schedule;
    public $currentApproved;

    public $encryptedDate;

    public $min = '00:00';
    public $max = '23:30';

    protected $listeners = [
        'schedulesCreateItemUpdateCheckOut' => 'updateCheckOut',
    ];

    // public $recentlyDeleted = false;

    public $isFirstInLoop = false;

    public function rules()
    {
        $rules = [
            'schedule.start' => 'nullable',
            'schedule.end' => 'nullable',
            'schedule.available' => 'required|boolean',
            'schedule.check_out' => '',
            'schedule.eats_onsite.breakfast' => 'required|boolean',
            'schedule.eats_onsite.lunch' => 'required|boolean',
            'schedule.eats_onsite.dinner' => 'required|boolean',
        ];

        // if ($this->needsTimeSchedule()) {
        //     $rules['schedule.start'] = 'required';
        //     $rules['schedule.end'] = 'required';
        // }

        return $rules;
    }

    public function mount(Schedule $schedule, $openingTimes = [], $firstInLoop = false)
    {
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
        $date = Carbon::parse($this->schedule->date);

        $day = $date->day;
        if ($day < 10) {
            $day = '0'.$day;
        }

        return $day.'. '.$date->shortMonthName;
    }

    public function updateCheckOut($date, $cause)
    {
        if ($date != decrypt($this->encryptedDate)) {
            return;
        }

        $this->schedule->check_out = $cause;
        $this->schedule->available = false;
        $this->schedule->start = null;
        $this->schedule->end = null;

        $this->schedule->eats_onsite = [
            'breakfast' => false,
            'lunch' => false,
            'dinner' => false,
        ];
    }

    public function saveSchedule($fromWhere = null)
    {
        // if ($this->recentlyDeleted) {
        //     $this->recentlyDeleted = false;

        //     return;
        // }

        if (! $this->schedule->date) {
            $this->schedule->date = decrypt($this->encryptedDate);
        }

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

        if ($fromWhere == 'available') {
            $this->schedule->eats_onsite = $this->getCurrentApprovedEatsOnsite();
        } else {
            $this->schedule->eats_onsite = $this->getEatsOnsiteBasedOnOrg($this->schedule->eats_onsite);
        }

        if ($this->schedule->getKey()) {
            $alreadyHasSchedule = Schedule::query()
                ->where('user_id', auth()->id())
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
                ->where('user_id', auth()->id())
                ->where('date', decrypt($this->encryptedDate))
                ->first();

            if ($alreadyHasSchedule) {
                $this->schedule = $alreadyHasSchedule;

                return $this->emitMessage('error', trans('schedules.duplicate_entry_error'));
            } else {
                $this->schedule->user_id = auth()->id();
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
            $this->schedule->status = 'approved';
            $this->schedule->current_approved = null;
            $this->setCurrentApproved();
        }

        $this->checkCanAutoApproveIfDataMatch();
        $this->schedule->save();

        $addCustomJob && $this->schedule->status == 'pending' && $this->addNotificationToCustomJob();

        if ($this->schedule->status == 'approved') {
            auth()->user()->jobs()
                ->whereHasMorph('related', Schedule::class)
                ->where('related_id', $this->schedule->id)
                ->where('action', AttendanceNotification::class)
                ->delete();

            $this->schedule->relatedNotifications()
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                ]);
        }
    }

    private function addNotificationToCustomJob()
    {
        $users = User::query()
            ->whereIn('users.id', function ($query) {
                $query->select('user_id')
                    ->from('group_user')
                    ->whereIn('group_user.group_id', function ($query) {
                        $query->select('group_id')
                            ->from('group_user')
                            ->where('group_user.user_id', auth()->id());
                    });
            })
            ->where('users.role', 'Principal')
            ->where('users.organization_id', auth()->user()->organization_id)
            ->pluck('users.id')
            ->toArray();

        if ($users && count($users)) {
            CustomJob::query()
                ->where('related_type', Schedule::class)
                ->where('related_id', $this->schedule->id)
                ->where('action', UserAttendanceDealedNotification::class)
                ->delete();

            // TODO: set priority for custom job depending on date of schedule

            auth()->user()->jobs()->updateOrCreate([
                'related_type' => Schedule::class,
                'related_id' => $this->schedule->id,
                'action' => AttendanceNotification::class,
            ], [
                'user_ids' => $users,
                'due_at' => now()->addMinutes(2),
                'data' => [],
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
            ->where('user_id', auth()->id())
            ->where('date', decrypt($this->encryptedDate))
            ->first();

        if (! $alreadyHasSchedule) {
            return;
        }

        if ($scheduleUuid && $scheduleUuid != $alreadyHasSchedule->uuid) {
            return;
        }

        $this->schedule = $alreadyHasSchedule;

        $this->schedule->update([
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
            ->where('related_id', $this->id)
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

        $start = Carbon::parse($this->schedule->date.' '.$this->schedule->start);
        $end = Carbon::parse($this->schedule->date.' '.$this->schedule->end);

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
