<?php

namespace Tests\Traits;

use App\Models\CalendarEvent;
use App\Models\Group;
use App\Models\Information;
use App\Models\Organization;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Livewire\Livewire;

trait TestHelpersTraits
{
    private function getUser($type = 'User', $org = null, $settings = [])
    {
        return User::factory()->create([
            'role' => $type,
            'organization_id' => $org,
            'settings' => $settings,
        ]);
    }

    private function createUserLog($user, $type = 'enter', $scheduleId = 1)
    {
        UserLog::create([
            'user_id' => $user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $scheduleId,
            'type' => $type,
            'trigger_type' => 'terminal',
        ]);
    }

    private function createCalendarEvent($title, $user, $groups = [], $from = null, $to = null)
    {
        if (! $from) {
            $from = now()->addDay()->addHours(10);
        }

        $to = $from->copy()->addHours(5);

        CalendarEvent::create([
            'title' => $title,
            'description' => Str::repeat('a', random_int(50, 100)),
            'all_day' => random_int(0, 1),
            'from' => $from,
            'to' => $to,
            'groups' => $groups,
            'roles' => ['User', 'Principal', 'Manager'],
            'organization_id' => $user->organization_id,
            'creator_id' => $user->id,
        ]);
    }

    private function createInformation($title, $creator, $org, $roles = ['User', 'Manager', 'Principal'])
    {
        Information::create([
            'title' => $title,
            'roles' => $roles,
            'file' => File::fake()->image('wow.jpg'),
            'creator_id' => $creator->id,
            'organization_id' => $org->id,
        ]);
    }

    private function createUsers($howMany, $attributes = ['organization_id' => null])
    {
        return User::factory()
            ->count($howMany)
            ->create($attributes);
    }

    private function getOrgGroupUser($type = 'User', $settings = [])
    {
        $org = Organization::factory()->create([
            'name' => 'My org name',
        ]);

        $group = Group::factory()->create([
            'name' => 'My First Group',
            'organization_id' => $org->id,
        ]);
        $user = $this->getUser($type, $org->id, $settings);

        if (in_array($type, ['User', 'Principal'])) {
            $user->groups()->attach($group);
        }

        return [
            $org,
            $group,
            $user,
        ];
    }

    private function getPagesCount($data)
    {
        $total = floor($data->count() / config('setting.perPage'));
        $extra = $data->count() % config('setting.perPage');

        return [$total, $extra];
    }

    private function createTestScheduleData($user, $date, $start = null, $end = null, $status = 'pending', $available = true)
    {
        $eats_onsite = [
            'breakfast' => random_int(0, 1),
            'lunch' => random_int(0, 1),
            'dinner' => random_int(0, 1),
        ];

        return $user->schedules()->save(
            Schedule::factory()->make(compact('date', 'start', 'end', 'status', 'eats_onsite', 'available'))
        );
    }

    private function setLeadAndSelectionTime($org, $leadTime = 0, $selectionTime = 10)
    {
        $orgSettings = $org->settings;
        $orgSettings['limitations'] = [
            'lead_time' => $leadTime,
            'selection_time' => $selectionTime,
        ];
        $org->update([
            'settings' => $orgSettings,
        ]);
    }

    private function setUserAvailability($user, $status = 'available')
    {
        $userSettings = $user->settings;
        if (! $userSettings) {
            $userSettings = [];
        }

        $userSettings['availability'] = $status;

        $user->update([
            'settings' => $userSettings,
        ]);
    }

    private function removeSecondsFromTime($time)
    {
        if ($time && Str::length($time) === 8) {
            return (string) Str::of($time)->replaceLast(':00', '');
        }

        return $time;
    }

    private function addEvent($groups = [], $roles = [])
    {
        $upload1 = UploadedFile::fake()->create($this->firstUploads[0], 100, 'application/pdf');
        $upload2 = UploadedFile::fake()->create($this->firstUploads[1], 100, 'application/pdf');

        $livewire = Livewire::test('calendar.create')
            ->set('event.title', $this->event->title)
            ->set('event.description', $this->event->description)
            ->set('event.from', $this->event->from)
            ->set('event.to', $this->event->to)
            ->set('event.all_day', $this->event->all_day)
            ->set('event.color', $this->event->color)
            ->set('files.0', $upload1)
            ->set('files.1', $upload2)
            ->set("roles.Manager", false)
            ->set("roles.Vendor", false)
            ->set("roles.Principal", false)
            ->set("roles.User", false)
            ->set('groups_id', $groups);

        foreach ($roles as $role) {
            $livewire->set("roles.{$role}", true);
        }

        $livewire->call('submit');
    }
}
