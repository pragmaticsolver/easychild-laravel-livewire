<?php

namespace Tests\Traits;

use App\Models\Group;
use App\Models\Organization;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Str;

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

    private function createUsers($howMany, $attributes = ['organization_id' => null])
    {
        return User::factory()
            ->count($howMany)
            ->create($attributes);
    }

    private function getOrgGroupUser($type = 'User', $settings = [])
    {
        $org = Organization::factory()->create();

        $org->groups()->save(Group::factory()->make([
            'name' => 'My First Group',
        ]));
        $user = $this->getUser($type, $org->id, $settings);

        $group = $org->groups->first();
        $group->users()->attach($user);

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

    private function createTestScheduleData($user, $date, $start, $end, $status = 'pending')
    {
        $eats_onsite = false;

        return $user->schedules()->save(
            Schedule::factory()->make(compact('date', 'start', 'end', 'status', 'eats_onsite'))
        );
    }

    private function removeSecondsFromTime($time)
    {
        if ($time && Str::length($time) === 8) {
            return (string) Str::of($time)->replaceLast(':00', '');
        }

        return $time;
    }
}
