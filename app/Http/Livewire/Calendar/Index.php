<?php

namespace App\Http\Livewire\Calendar;

use App\Http\Livewire\Component;
use App\Models\CalendarEvent;
use App\Models\CustomJob;
use App\Models\Group;
use App\Models\User;
use App\Notifications\CalendarEventNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    public $date;

    protected $queryString = [
        'date',
    ];

    protected $listeners = [
        'refreshContent' => '$refresh',
    ];

    public function getThemeColorsProperty()
    {
        return collect(config('setting.events.colors'));
    }

    public function mount()
    {
        if (! $this->date) {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function next()
    {
        $date = Carbon::parse($this->date);
        $date->addWeek();

        $this->date = $date->format('Y-m-d');
    }

    public function previous()
    {
        $date = Carbon::parse($this->date);
        $date->subWeek();

        $this->date = $date->format('Y-m-d');
    }

    public function getPageTitleProperty()
    {
        return trans('calendar-events.page_title');
    }

    public function getCurrentWeekProperty()
    {
        $date = Carbon::parse($this->date);

        return trans('calendar-events.page_week_no', [
            'week' => $date->weekOfYear,
        ]);
    }

    public function deleteEvent($eventUuid)
    {
        $event = CalendarEvent::findByUUIDOrFail($eventUuid);
        $this->authorize('delete', $event);

        $eventTitle = $event->title;

        $this->removeEventTrace($event);

        $event->delete();

        $this->emitMessage('success', trans('calendar-events.delete_success', [
            'title' => $eventTitle,
        ]));
    }

    private function removeEventTrace(CalendarEvent $event)
    {
        if (Storage::disk('events')->exists($event->uuid)) {
            Storage::disk('events')->deleteDirectory($event->uuid);
        }

        CustomJob::query()
            ->where('related_type', CalendarEvent::class)
            ->where('related_id', $event->id)
            ->where('action', CalendarEventNotification::class)
            ->delete();

        $event->relatedNotifications()
            ->delete();
    }

    public function render()
    {
        $startDate = Carbon::parse($this->date)->startOfWeek();
        $endDate = Carbon::parse($this->date)->endOfWeek();

        $user = auth()->user();
        $events = CalendarEvent::query()
            ->forUser()
            ->withStartAndEndPeriod($startDate, $endDate)
            ->with('birthdayUser')
            ->get();

        $groups = Group::query()
            ->get(['id', 'name']);

        $childrens = collect();

        if ($user->isParent()) {
            $childrens = User::query()
                ->where('role', 'User')
                ->addSelect([
                    'group_id' => DB::table('group_user')
                        ->whereColumn('group_user.user_id', 'users.id')
                        ->select('group_user.group_id')
                        ->limit(1),
                ])
                ->whereIn('users.id', function ($query) use ($user) {
                    $query->select('parent_child.child_id')
                        ->from('parent_child')
                        ->where('parent_child.parent_id', $user->id);
                })->get();

            foreach ($events as $eventItem) {
                if (! $eventItem->birthday) {
                    $childAvatars = [];

                    if ($eventItem->groups && count($eventItem->groups)) {
                        foreach ($childrens as $child) {
                            if (in_array($child->group_id, $eventItem->groups)) {
                                $childAvatars[] = $child->avatar_url;
                            }
                        }
                    } else {
                        foreach ($childrens as $child) {
                            if ($child->organization_id == $eventItem->organization_id) {
                                $childAvatars[] = $child->avatar_url;
                            }
                        }
                    }

                    $eventItem->setRelation('childAvatars', $childAvatars);
                }
            }
        }

        $eventItems = [];

        do {
            if ($startDate->isWeekday()) {
                $eventsForDate = $events->filter(function ($value, $key) use ($startDate) {
                    return ($startDate->isSameDay($value->from) || $startDate->isSameDay($value->to)) || ($startDate >= $value->from && $startDate <= $value->to);
                });

                $eventItems[] = [
                    'events' => $eventsForDate,
                    'date' => $startDate->copy(),
                ];
            }

            $startDate->addDays(1);
        } while ($startDate <= $endDate);

        return view('livewire.calendar.index', compact('eventItems', 'groups'));
    }
}
