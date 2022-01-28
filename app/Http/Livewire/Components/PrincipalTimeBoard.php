<?php

namespace App\Http\Livewire\Components;

use App\Events\ScheduleUpdated;
use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PrincipalTimeBoard extends Component
{
    public $absentUsers;
    public $presentUsers;
    public $collectorPopup;

    protected $listeners = [
        'principalGroupUpdated' => 'updateSchedulesList',
        'principal-presence-end' => 'setPresenceEnd'
    ];

    public function mount()
    {
        $org = auth()->user()->organization;
        $settings = $org->settings;
        $this->collectorPopup = $this->getValueByKey($settings, 'collectorPopup', false);
        $this->updateSchedulesList();
    }

    public function updateSchedulesList()
    {
        [$schedules, $group] = Schedule::todaysSchedule();

        $schedules = collect($schedules->toArray());

        $schedules = $schedules
            ->whereNotNull('uuid')
            ->where('status', 'approved')
            ->where('available', true);

        $presentUsers = $schedules
            ->whereNotNull('presence_start')
            ->whereNull('presence_end');

        $absentUsers = $schedules
            ->whereNotIn('uuid', $presentUsers->pluck('uuid'));

        $this->presentUsers = $this->getOnlySpecificKeys($presentUsers);
        $this->absentUsers = $this->getOnlySpecificKeys($absentUsers);
    }

    private function getOnlySpecificKeys($data)
    {
        $items = $data->toArray();

        $newItems = [];
        foreach ($items as $item) {
            $newItems[] = [
                'uuid' => $item['uuid'],
                'user_name' => $item['user_name'],
                'user_uuid' => $item['user_uuid'],
            ];
        }

        return collect($newItems);
    }

    public function setPresenceStart($scheduleUuid)
    {
        $now = now();

        $schedule = Schedule::findByUUIDOrFail($scheduleUuid);
        $this->authorize('update', $schedule);

        $schedule->update([
            'presence_start' => $now->format('H:i'),
            'presence_end' => null,
        ]);

        [$from, $to] = $this->transferItemFromOneToAnother($this->absentUsers, $this->presentUsers, $schedule->uuid);

        $this->absentUsers = $from;
        $this->presentUsers = $to;

        ScheduleUpdated::dispatch($schedule, [
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => auth()->id(),
        ]);

        $this->sendDashboardUpdateEvent();
    }

    public function showPresentModal($uuid, $user_uuid, $user_name)
    {
        $schedule = Schedule::findByUUIDOrFail($uuid);

        $lastTrigger = UserLog::query()
            ->where('user_id', $schedule->user_id)
            ->where('type', 'enter')
            ->whereHasMorph('typeable', [Schedule::class], function ($query) use ($schedule) {
                $query->where('typeable_id', $schedule->id);
            })->latest()->first();


        if ($lastTrigger && now()->subMinutes(15) >= $lastTrigger->created_at && $this->collectorPopup) {
            $this->emitTo('components.present-modal', 'present-show-modal',  [
                'uuid' => $uuid,
                'user_uuid' => $user_uuid,
                'user_name' => $user_name,
                'from_action' => 'dashboard'
            ]);
        } else {
            $user = User::findByUuid($user_uuid);
            $this->setPresenceEnd([
                'schedule_uuid' => $uuid,
                'contact_name' => '',
                'user_id' => $user->id
            ]);
        }
    }

    public function setPresenceEnd($params)
    {
        $scheduleUuid = $params['schedule_uuid'];
        $contactName = $params['contact_name'];
        $userId = $params['user_id'];
        $now = now();

        $schedule = Schedule::findByUUIDOrFail($scheduleUuid);
        $this->authorize('update', $schedule);

        $schedule->update([
            'presence_end' => $now->format('H:i'),
        ]);

        [$from, $to] = $this->transferItemFromOneToAnother($this->presentUsers, $this->absentUsers, $schedule->uuid);

        $this->presentUsers = $from;
        $this->absentUsers = $to;

        ScheduleUpdated::dispatch($schedule, [
            'type' => 'leave',
            'trigger_type' => 'user',
            'triggred_id' => auth()->id(),
            'note' => $contactName != '' ? trans('attendances.collected_by', ['name' => $contactName]) : null
        ]);

//        UserLog::create([
//            'user_id' => $userId,
//            'typeable_type' => Schedule::class,
//            'typeable_id' => $schedule->id,
//            'type' => 'leave',
//            'trigger_type' => 'user',
//            'triggred_id' => auth()->id(),
//            'note' => $contactName
//        ]);

        $this->sendDashboardUpdateEvent();
    }

    private function transferItemFromOneToAnother($from, $to, $scheduleUuid)
    {
        $isAvailable = $from->where('uuid', $scheduleUuid)->first();
        $alreadyPresent = $to->where('uuid', $scheduleUuid)->first();

        if ($isAvailable && ! $alreadyPresent) {
            $to->push($isAvailable);
            $from = $from->where('uuid', '!=', $scheduleUuid);
        }

        return [
            $from,
            $to,
        ];
    }

    private function sendDashboardUpdateEvent()
    {
        if (Str::startsWith(request()->server('HTTP_REFERER'), route('dashboard'))) {
            list($timeSchedules, $prescenseSchedule, $mealPlans) = auth()->user()->getPrincipalDashboardDetail();

            $this->dispatchBrowserEvent('principal-dashboard-time-schedules', $timeSchedules);
            $this->dispatchBrowserEvent('principal-dashboard-prescense-schedules', $prescenseSchedule);

            foreach ($mealPlans as $key => $meal) {
                $this->dispatchBrowserEvent("principal-dashboard-meal-plan-{$key}", $meal);
            }
        }
    }

    public function render()
    {
        return view('livewire.components.principal-time-board');
    }
}
