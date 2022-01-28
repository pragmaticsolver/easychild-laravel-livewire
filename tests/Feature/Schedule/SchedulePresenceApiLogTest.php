<?php

namespace Tests\Feature\Schedule;

use App\Models\Schedule;
use App\Models\UserLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SchedulePresenceApiLogTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendanceToken = Str::random(32);

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser('User', [
            'attendance_token' => $this->attendanceToken,
        ]);
        $this->principal = $this->getUser('Manager', $this->org);
        $this->group->users()->attach($this->principal);

        $this->be($this->principal);
    }

    /** @test */
    public function enter_user_log_is_added_when_user_schedule_is_set_to_presence()
    {
        $schedule = $this->createTestScheduleData($this->user, now());

        $this->post("api/user/{$this->attendanceToken}/attend")
            ->assertNoContent();

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'enter',
            'trigger_type' => 'terminal',
            'triggred_id' => null,
        ]);
    }

    /** @test */
    public function leave_user_log_is_added_when_user_schedule_is_set_to_presence_end()
    {
        TestTime::freeze();

        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->save();

        UserLog::create([
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
        ]);

        TestTime::addMinutes(20);

        $this->post("api/user/{$this->attendanceToken}/leave")
            ->assertNoContent();

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'leave',
            'trigger_type' => 'terminal',
            'triggred_id' => null,
        ]);
    }

    /** @test */
    public function cannot_put_schedule_to_presence_end_when_its_already_in_absence_state()
    {
        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->presence_end = now()->addHour()->format('H:i');
        $schedule->save();

        $this->post("api/user/{$this->attendanceToken}/leave")
            ->assertStatus(422);
    }

    /** @test */
    public function clears_log_for_enter_if_leave_is_less_than_fifteen_minutes()
    {
        TestTime::freeze();

        $schedule = $this->createTestScheduleData($this->user, now());
        $schedule->presence_start = now()->format('H:i');
        $schedule->save();

        UserLog::create([
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
        ]);

        TestTime::addMinutes(10);

        $this->post("api/user/{$this->attendanceToken}/leave")
            ->assertNoContent();

        $this->assertDatabaseCount('user_logs', 0);
        $this->assertDatabaseHas('schedules', [
            'uuid' => $schedule->uuid,
            'presence_start' => null,
            'presence_end' => null,
        ]);
    }
}
