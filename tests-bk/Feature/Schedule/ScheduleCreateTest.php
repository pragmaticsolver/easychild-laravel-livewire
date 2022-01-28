<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleCreateTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_role_can_access_schedule_create_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'name' => 'My First Group',
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $this->get(route('schedules.index'))
            ->assertSeeTextInOrder([
                trans('schedules.create_title'),
                $org->name,
                $user->full_name,
                $user->userGroup() ? $user->userGroup()->name : 'N/A',
            ]);
    }

    /** @test */
    public function user_check_if_first_item_on_create_list_is_always_for_weekday()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedule = collect($response->viewData('schedules'))->first();

        $this->assertTrue(Carbon::parse($schedule['date'])->isWeekday());
    }

    /** @test */
    public function user_check_cannot_update_disabled_schedule()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedule = collect($response->viewData('schedules'))->first();
        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->call('saveSchedule', ['start' => '10:00'])
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.disabled_schedule'),
            ]);
    }

    /** @test */
    public function user_cannot_update_the_schedule_without_providing_start_time()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();
        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->call('saveSchedule', [
                'start' => '',
                'end' => '10:30',
            ])
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.start_required'),
            ]);
    }

    /** @test */
    public function user_cannot_update_the_schedule_without_providing_end_time()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', '10:30')
            ->set('end', '')
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.end_required'),
            ]);
    }

    /** @test */
    public function user_cannot_update_the_schedule_when_start_time_is_greater_than_end_time()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', '10:30')
            ->set('end', '09:00')
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.start_earlier_than_end'),
            ]);
    }

    /** @test */
    public function user_cannot_update_the_schedule_when_start_time_is_less_than_org_opening_time()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), -1);

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', $startTime)
            ->set('end', '09:00')
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.start_min_max_limit', [
                    'min' => config('setting.defaultOpeningTimes.start'),
                    'max' => config('setting.defaultOpeningTimes.end'),
                ]),
            ]);
    }

    /** @test */
    public function user_cannot_update_the_schedule_when_start_time_is_greater_than_org_closing_time()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.end'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.end'), 3);

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', $startTime)
            ->set('end', $endTime)
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.start_min_max_limit', [
                    'min' => config('setting.defaultOpeningTimes.start'),
                    'max' => config('setting.defaultOpeningTimes.end'),
                ]),
            ]);
    }

    /** @test */
    public function user_cannot_update_the_schedule_when_end_time_is_greater_than_org_closing_time()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.end'), -1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.end'), 3);

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', $startTime)
            ->set('end', $endTime)
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.end_min_max_limit', [
                    'min' => config('setting.defaultOpeningTimes.start'),
                    'max' => config('setting.defaultOpeningTimes.end'),
                ]),
            ]);
    }

    /** @test */
    public function user_can_see_schedules_status_when_on_schedules_page()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 3);

        for ($x = 0; $x < 20; $x++) {
            $date = $knownDate->copy()->addDays($x);

            if ($date->isWeekday()) {
                $this->createTestScheduleData($user, $date->format('Y-m-d'), $startTime, $endTime);
            }
        }

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();
        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->assertSet('status', 'pending');
    }

    /** @test */
    public function user_can_see_schedules_eats_onsite_when_on_schedules_page()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 3);

        for ($x = 0; $x < 20; $x++) {
            $date = $knownDate->copy()->addDays($x);

            if ($date->isWeekday()) {
                $this->createTestScheduleData($user, $date->format('Y-m-d'), $startTime, $endTime);
            }
        }

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();
        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->assertSet('eatsOnsite', false);
    }

    /** @test */
    public function user_can_update_schedules_eats_onsite_when_on_schedules_page()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 3);

        for ($x = 0; $x < 20; $x++) {
            $date = $knownDate->copy()->addDays($x);

            if ($date->isWeekday()) {
                $this->createTestScheduleData($user, $date->format('Y-m-d'), $startTime, $endTime);
            }
        }

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 2);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 4);

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', $startTime)
            ->set('end', $endTime)
            ->set('eatsOnsite', true)
            ->call('saveSchedule')
            ->assertSet('eatsOnsite', true)
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', [
                    'day' => $schedule['date'],
                ]),
            ]);

        $this->assertDatabaseHas('schedules', [
            'date' => $schedule['date'],
            'start' => $startTime,
            'end' => $endTime,
            'eats_onsite' => true,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_can_update_schedules_start_or_end_time_when_on_schedules_page()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 3);

        for ($x = 0; $x < 20; $x++) {
            $date = $knownDate->copy()->addDays($x);

            if ($date->isWeekday()) {
                $this->createTestScheduleData($user, $date->format('Y-m-d'), $startTime, $endTime);
            }
        }

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->whereNotNull('index')->last();

        $schedule['start'] = $schedule['start'] . ':00';
        $schedule['end'] = $schedule['end'] . ':00';

        $openingTimes = $response->viewData('openingTimes');

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 2);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 4);

        $res = Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', $startTime)
            ->set('end', $endTime)
            ->call('saveSchedule')
            ->assertSet('start', $startTime)
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', [
                    'day' => $schedule['date'],
                ]),
            ]);
    }

    /** @test */
    public function user_can_create_schedules_start_or_end_time_when_on_schedules_page()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 3);

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();

        $openingTimes = $response->viewData('openingTimes');

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 2);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 4);

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->set('start', $startTime)
            ->set('end', $endTime)
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.create_success', [
                    'day' => $schedule['date'],
                ]),
            ])
            ->assertSet('status', 'pending');

        $schedules = $user->schedules()->where('date', $schedule['date'])->get();
        $this->assertEquals(1, $schedules->count());
    }

    /** @test */
    public function user_can_remove_schedules_when_on_schedules_page()
    {
        $knownDate = now()->startOfWeek();
        Carbon::setTestNow($knownDate->copy()->subDay());

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user);

        $startTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 1);
        $endTime = $this->getTimePlusOrMinus(config('setting.defaultOpeningTimes.start'), 3);

        for ($x = 0; $x < 20; $x++) {
            $date = $knownDate->copy()->addDays($x);

            if ($date->isWeekday()) {
                $this->createTestScheduleData($user, $date->format('Y-m-d'), $startTime, $endTime);
            }
        }

        $response = Livewire::test('schedules.create');

        $schedules = collect($response->viewData('schedules'));
        $schedule = $schedules->where('index', 10)->first();
        $openingTimes = $response->viewData('openingTimes');

        Livewire::test('schedules.create-item', compact('schedule', 'openingTimes'))
            ->call('removeSchedule')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.remove_success', [
                    'day' => $schedule['date'],
                ]),
            ]);

        $schedules = $user->schedules()->where('date', $schedule['date'])->get();
        $this->assertEquals(0, $schedules->count());
    }

    private function getTimePlusOrMinus($time, $offset)
    {
        return Carbon::parse($time)->addHour($offset)->format('H:i');
    }
}
