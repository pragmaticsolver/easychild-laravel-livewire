<?php

namespace App\Jobs;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChildrenBirthdayEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        if ($this->user->dob) {
            $this->updateOrCreateBirthdayCalendarEvent();

            return;
        }

        if ($event = $this->getBirthdayEvent()) {
            $event->delete();
        }
    }

    private function updateOrCreateBirthdayCalendarEvent()
    {
        $date = now()
            ->setMonth($this->user->dob->month)
            ->setDay($this->user->dob->day)
            ->startOfDay();

        CalendarEvent::updateOrCreate([
            'birthday' => true,
            'birthday_id' => $this->user->id,
        ], [
            'all_day' => true,
            'from' => $date,
            'to' => $date,
            'color' => 'green',
            'groups' => [$this->user->groups()->first()->id],
            'roles' => ['Manager', 'Principal', 'User'],
            'organization_id' => $this->user->organization_id,
        ]);
    }

    private function getBirthdayEvent()
    {
        return CalendarEvent::query()
            ->where('birthday', true)
            ->where('birthday_id', $this->user->id)
            ->first();
    }
}
