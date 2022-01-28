<?php

namespace Tests\Feature\Schedule;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SchedulePresenceLogEditorTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->principal = $this->getUser('Principal', $this->org);
        $this->group->users()->attach($this->principal);

        $this->extraPrincipal = $this->getUser('Principal', $this->org);
    }

    /** @test */
    public function principal_not_in_same_group_cannot_edit_log_for_previous_day()
    {
        $this->be($this->extraPrincipal);

        TestTime::freeze(now()->startOfWeek()->addDay()->addHours(10));

        $schedule = $this->createTestScheduleData($this->user, now()->subDay());

        Livewire::test('group-class.index', [
            'date' => now()->subDay()->format('Y-m-d'),
        ])
            ->emit('editSchedulePresenceTime', $schedule->uuid)
            ->assertUnauthorized();

        $this->assertDatabaseCount('user_logs', 0);
    }

    /** @test */
    public function can_edit_log_for_previous_day()
    {
        $this->be($this->principal);
        TestTime::freeze(now()->startOfWeek()->addDay()->addHours(10));

        $schedule = $this->createTestScheduleData($this->user, now()->subDay());

        Livewire::test('group-class.index', [
            'date' => now()->subDay()->format('Y-m-d'),
        ])
            ->emit('editSchedulePresenceTime', $schedule->uuid)
            ->assertSet('showEditScheduleForm', true)
            ->set('startTime', '09:15')
            ->set('endTime', '10:15')
            ->call('submitScheduleUpdate');

        $this->assertDatabaseCount('user_logs', 2);
        $this->assertEquals('09:15:00', $schedule->refresh()->presence_start);
        $this->assertEquals('10:15:00', $schedule->refresh()->presence_end);
    }

    /** @test */
    public function can_edit_log_for_current_day()
    {
        $this->be($this->principal);
        TestTime::freeze(now()->startOfWeek()->addDay()->addHours(10));

        $schedule = $this->createTestScheduleData($this->user, now());

        Livewire::test('group-class.index')
            ->emit('editSchedulePresenceTime', $schedule->uuid)
            ->assertSet('showEditScheduleForm', true)
            ->set('startTime', '09:15')
            ->set('endTime', '10:15')
            ->call('submitScheduleUpdate');

        $this->assertDatabaseCount('user_logs', 2);
        $this->assertEquals('09:15:00', $schedule->refresh()->presence_start);
        $this->assertEquals('10:15:00', $schedule->refresh()->presence_end);
    }

    /** @test */
    public function cannot_edit_log_for_next_day()
    {
        $this->be($this->principal);
        TestTime::freeze(now()->startOfWeek()->addDay()->addHours(10));

        $schedule = $this->createTestScheduleData($this->user, now()->addDay());

        Livewire::test('group-class.index')
            ->emit('editSchedulePresenceTime', $schedule->uuid)
            ->assertUnauthorized();
    }

    /** @test */
    public function cannot_edit_log_for_schedule_older_than_a_week()
    {
        $this->be($this->principal);
        TestTime::freeze(now()->startOfWeek()->addDay()->addHours(10));

        $schedule = $this->createTestScheduleData($this->user, now()->subWeek()->subDay());

        Livewire::test('group-class.index')
            ->emit('editSchedulePresenceTime', $schedule->uuid)
            ->assertUnauthorized();
    }
}
