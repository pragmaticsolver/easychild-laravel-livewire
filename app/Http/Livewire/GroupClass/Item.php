<?php

namespace App\Http\Livewire\GroupClass;

use App\Events\ScheduleUpdated;
use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Item extends Component
{
    public $uuid;
    public $start;
    public $end;
    public $presenceStart;
    public $presenceEnd;
    public $userName;
    public $userUuid;
    public $groupName;
    public $available;
    public $status;
    public $eats_onsite;
    public $allergy;
    public $collectorPopup;

    public $date;

    public User $user;

    public function getListeners()
    {
        $listeners = [];

        if ($this->uuid) {
            $listeners[$this->uuid] = 'updateScheduleData';
        }

        return $listeners;
    }

    public function updateScheduleData($schedule)
    {
        $this->uuid = $schedule['uuid'];
        $this->available = $schedule['available'];
        $this->status = $schedule['status'];
        $this->allergy = $schedule['allergy'];
        $this->eats_onsite = $schedule['eats_onsite'];
        $this->start = $this->removeSecondsFromTime($schedule['start']);
        $this->end = $this->removeSecondsFromTime($schedule['end']);
        $this->presenceStart = $this->removeSecondsFromTime($schedule['presence_start']);
        $this->presenceEnd = $this->removeSecondsFromTime($schedule['presence_end']);

        $this->date = encrypt($schedule['date']);
    }

    public function mount($schedule, $popup_active_scheduleUuid = null, $presenceEnd = null)
    {
        $this->updateScheduleData($schedule);

        $this->userName = $schedule['user']->full_name;
        $this->userUuid = $schedule['user']->uuid;
        $this->user = $schedule['user'];
        $this->groupName = $schedule['group_name'];

        $org = auth()->user()->organization;
        $settings = $org->settings;
        $this->collectorPopup = $this->getValueByKey($settings, 'collectorPopup', false);
        if(!empty($popup_active_scheduleUuid))
        {
            $this->presenceEnd = $presenceEnd;
        }
    }

    public function setPresenceStart()
    {
        if (! $this->isCurrentDay) {
            return false;
        }

        abort_if(! $this->startBtnEnabled(), 401);

        $now = now();
        $this->presenceStart = $now->format('H:i');
        $this->presenceEnd = null;
        $this->available = true;

        if ($this->uuid) {
            $schedule = Schedule::findByUUIDOrFail($this->uuid);
            $this->authorize('update', $schedule);

            $schedule->update([
                'available' => true,
                'presence_start' => $this->presenceStart,
                'presence_end' => $this->presenceEnd,
            ]);
        } else {
            $schedule = Schedule::updateOrCreate(
                [
                    'user_id' => $this->user->id,
                    'date' => now()->format('Y-m-d'),
                ],
                [
                    'available' => true,
                    'status' => 'approved',
                    'presence_start' => $this->presenceStart,
                    'presence_end' => $this->presenceEnd,
                    'allergy' => $this->allergy,
                    'eats_onsite' => $this->eats_onsite,
                ]
            );

            $this->uuid = $schedule->uuid;
        }

        ScheduleUpdated::dispatch($schedule, [
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => auth()->id(),
        ]);

        $this->emitUp('userPresenceUpdated');
    }

    public function showPresenceEndModal()
    {
        $schedule = Schedule::findByUUIDOrFail($this->uuid);

        $lastTrigger = UserLog::query()
            ->where('user_id', $schedule->user_id)
            ->where('type', 'enter')
            ->whereHasMorph('typeable', [Schedule::class], function ($query) use ($schedule) {
                $query->where('typeable_id', $schedule->id);
            })->latest()->first();

        if ($lastTrigger && now()->subMinutes(15) >= $lastTrigger->created_at && $this->collectorPopup) {
            $this->emitTo('components.present-modal', 'present-show-modal',  [
                'uuid' => $this->uuid,
                'user_uuid' => $this->userUuid,
                'user_name' => $this->userName,
                'from_action' => 'presence'
            ]);
        } else {
            $this->setPresenceEnd([
                'contact_name' => ''
            ]);
        }
    }


    public function setPresenceEnd($params)
    {

        if (! $this->isCurrentDay) {
            return false;
        }

        $contactName = $params['contact_name'];

        abort_if(! $this->endBtnEnabled(), 401);

        $now = now();

        $this->presenceEnd = $now->format('H:i');

        $schedule = Schedule::findByUUIDOrFail($this->uuid);
        $this->authorize('update', $schedule);

        $schedule->update([
            'presence_end' => $this->presenceEnd,
        ]);

        ScheduleUpdated::dispatch($schedule, [
            'type' => 'leave',
            'trigger_type' => 'user',
            'triggred_id' => auth()->id(),
            'note' => $contactName != '' ? trans('attendances.collected_by', ['name' => $contactName]) : null
        ]);

        $this->emitUp('userPresenceUpdated');
    }

    public function getIsCurrentDayProperty()
    {
        $date = Carbon::parse(decrypt($this->date));

        return $date->isToday();
    }

    public function editScheduleTime()
    {
    }

    public function canEditSchedule()
    {
        $date = Carbon::parse(decrypt($this->date));

        return $date >= now()->subWeek() && $date < now()->endOfDay();
    }

    public function isPreviousDaySchedule()
    {
        $date = Carbon::parse(decrypt($this->date));

        return $date->startOfDay() < now()->endOfDay();
    }

    public function startBtnEnabled()
    {
        if (! $this->isCurrentDay) {
            return false;
        }

        if (is_null($this->presenceStart)) {
            return true;
        }

        if (! is_null($this->presenceEnd)) {
            return true;
        }

        return false;
    }

    public function endBtnEnabled()
    {
        if (! $this->isCurrentDay) {
            return false;
        }

        if (is_null($this->presenceStart)) {
            return false;
        }

        if (is_null($this->presenceEnd)) {
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

    public function render()
    {
        return view('livewire.group-class.item');
    }
}
