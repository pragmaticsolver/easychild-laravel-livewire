<?php

namespace App\Observers;

use App\Models\CalendarEvent;
use App\Models\User;
use App\Notifications\CalendarEventNotification;

class CalendarEventObserver
{
    public function created(CalendarEvent $calendarEvent)
    {
        if ($calendarEvent->birthday) {
            return;
        }

        $users = User::query()
            ->forCalendarEventAudience($calendarEvent)
            ->where('users.id', '!=', $calendarEvent->creator_id)
            ->pluck('users.id')
            ->toArray();

        if (
            $users && count($users)
            && auth()->user()
            && auth()->user()->organization_id == $calendarEvent->organization_id
        ) {
            auth()->user()->jobs()->updateOrCreate([
                'related_type' => CalendarEvent::class,
                'related_id' => $calendarEvent->id,
                'action' => CalendarEventNotification::class,
            ], [
                'user_ids' => $users,
                'due_at' => now()->addMinutes(1),
                'data' => [],
            ]);
        }
    }
}
