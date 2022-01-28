<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Component;

class SwitchGroup extends Component
{
    public $group;
    public $enabled;

    protected $listeners = [
        'showSwitchGroupModal',
    ];

    public function mount()
    {
        $this->group = auth()->user()->principal_current_group_id;

        $this->enabled = false;
    }

    public function showSwitchGroupModal()
    {
        $this->enabled = true;
    }

    public function changeGroup($group)
    {
        if ($this->group != $group) {
            $this->group = $group;
            auth()->user()->setPrincipalCurrentGroup($group);
            $this->emit('principalGroupUpdated');

            $this->dispatchBrowserEvent('principal-switch-group', auth()->user()->principal_current_group->name);

            $this->sendDashboardUpdateEvent();
        }

        $this->enabled = false;
    }

    private function sendDashboardUpdateEvent()
    {
        list($timeSchedules, $prescenseSchedule, $mealPlans) = auth()->user()->getPrincipalDashboardDetail();

        $this->dispatchBrowserEvent('principal-dashboard-time-schedules', $timeSchedules);
        $this->dispatchBrowserEvent('principal-dashboard-prescense-schedules', $prescenseSchedule);

        foreach ($mealPlans as $key => $meal) {
            $this->dispatchBrowserEvent("principal-dashboard-meal-plan-{$key}", $meal);
        }
    }

    public function render()
    {
        $groups = auth()->user()->groups;

        return view('livewire.components.switch-group', compact('groups'));
    }
}
