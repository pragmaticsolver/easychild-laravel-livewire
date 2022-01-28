<?php

namespace Tests\Feature\Schedule;

use App\Events\ScheduleUpdated;
use App\Listeners\AddLogsToAttendanceTable;
use App\Models\Schedule;
use App\Models\UserLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SchedulePresencePageLogTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->principal = $this->getUser('Principal', $this->org);
        $this->group->users()->attach($this->principal);

        $this->be($this->principal);
    }

    /** @test */
    public function schedule_updated_event_has_listeners()
    {
        Event::fake();

        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->group_name = $this->group->name;

        Livewire::test('group-class.item', compact('schedule'))
            ->call('setPresenceStart');

        Event::assertDispatched(ScheduleUpdated::class);

        Event::assertListening(
            ScheduleUpdated::class,
            AddLogsToAttendanceTable::class
        );
    }

    /** @test */
    public function enter_user_log_is_added_when_user_schedule_is_set_to_presence()
    {
        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->group_name = $this->group->name;

        Livewire::test('group-class.item', compact('schedule'))
            ->call('setPresenceStart');

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
        ]);
    }

    /** @test */
    public function leave_user_log_is_added_when_user_schedule_is_set_to_presence_end()
    {
        TestTime::freeze();

        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->save();

        $schedule->group_name = $this->group->name;

        UserLog::create([
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
        ]);

        TestTime::addMinutes(20);

        Livewire::test('group-class.item', compact('schedule'))
            ->call('setPresenceEnd');

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'leave',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
        ]);
    }

    /** @test */
    public function cannot_put_schedule_to_presence_when_its_already_in_presence_state()
    {
        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->save();

        $schedule->group_name = $this->group->name;

        Livewire::test('group-class.item', compact('schedule'))
            ->call('setPresenceStart')
            ->assertStatus(401);
    }

    /** @test */
    public function cannot_put_schedule_to_presence_end_when_its_already_in_absence_state()
    {
        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->presence_end = now()->addHour()->format('H:i');
        $schedule->save();

        $schedule->group_name = $this->group->name;

        Livewire::test('group-class.item', compact('schedule'))
            ->call('setPresenceEnd')
            ->assertStatus(401);
    }

    /** @test */
    public function clears_log_for_enter_if_leave_is_less_than_fifteen_minutes()
    {
        TestTime::freeze();

        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->save();

        $schedule->group_name = $this->group->name;

        UserLog::create([
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
        ]);

        TestTime::addMinutes(10);

        Livewire::test('group-class.item', compact('schedule'))
            ->call('setPresenceEnd');

        $this->assertDatabaseCount('user_logs', 0);
        $this->assertDatabaseHas('schedules', [
            'uuid' => $schedule->uuid,
            'presence_start' => null,
            'presence_end' => null,
        ]);
    }
}
