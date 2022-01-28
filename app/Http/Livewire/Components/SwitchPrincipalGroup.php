<?php

namespace App\Http\Livewire\Components;

use Illuminate\Support\Str;
use Livewire\Component;

class SwitchPrincipalGroup extends Component
{
    protected $listeners = [
        'switchPrincipalGroup',
    ];

    public function switchPrincipalGroup($groupUuid)
    {
        $group = auth()->user()->groups()
            ->where('groups.uuid', $groupUuid)
            ->first();

        if ($group) {
            auth()->user()->setPrincipalCurrentGroup($group->id);
            $this->emit('principalGroupUpdated');
            $this->dispatchBrowserEvent('principal-switch-group', auth()->user()->principal_current_group->name);

            $this->sendDashboardUpdateEvent();
        }
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
        return view('livewire.components.switch-principal-group');
    }
}
