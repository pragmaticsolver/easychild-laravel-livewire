<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class ICalendarController extends Controller
{
    public function __invoke(Request $request, $token = null)
    {
        abort_if(! $token, Response::HTTP_FORBIDDEN);

        $token = decrypt($token);

        $user = User::findByUUIDOrFail($token);

        $events = CalendarEvent::query()
            ->forUser($user)
            ->forICal()
            ->get();

        $events->load('birthdayUser');

        $calendar = Calendar::create()
            ->name(trans('calendar-events.ical.title', ['name' => $user->full_name]))
            ->description(trans('calendar-events.ical.description'))
            ->withoutAutoTimezoneComponents();

        foreach ($events as $event) {
            $title = $event->title;
            $description = $event->description;

            if ($event->birthday_id) {
                $title = $event->birthdayUser->full_name.'-'.trans('calendar-events.birthday_description');
                $description = trans('calendar-events.birthday_description');
            }

            $calendar->event(
                Event::create()
                    ->alertMinutesBefore(15, $event->title)
                    ->name($title)
                    ->description($description)
                    ->createdAt($event->created_at)
                    ->startsAt($event->from)
                    ->endsAt($event->to)
            );
        }

        return response($calendar->refreshInterval(1)->get())
            ->header('Content-Type', 'text/calendar')
            ->header('Content-Disposition', 'attachment; filename="easychild-events.ics"')
            ->header('charset', 'utf-8');
    }
}
