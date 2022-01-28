<?php

namespace App\Http\Livewire\Calendar;

use App\Http\Livewire\Component;
use App\Models\CalendarEvent;
use App\Models\Group;

class View extends Component
{
    public $event;
    public $showModal = false;

    public function mount()
    {
        $this->event = CalendarEvent::make();
    }

    public function open($eventUuid)
    {
        $this->event = CalendarEvent::make();

        $eventItem = CalendarEvent::findByUUIDOrFail($eventUuid);

        $this->authorize('view', $eventItem);

        if ($eventItem->birthday) {
            $eventItem->load('birthdayUser');
        }

        $this->event = $eventItem;

        $this->showModal = true;
    }

    public function getDateFormatProperty()
    {
        $dateFormat = config('setting.format.datetime');

        if ($this->event->all_day) {
            $dateFormat = config('setting.format.date');
        }

        return $dateFormat;
    }

    public function getGroupsProperty()
    {
        if ($this->event->groups && count($this->event->groups)) {
            $groups = Group::query()
                ->whereIn('id', $this->event->groups)
                ->where('organization_id', auth()->user()->organization_id)
                ->pluck('name')
                ->toArray();

            return $groups;
        }

        return [];
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.calendar.view');
    }
}
