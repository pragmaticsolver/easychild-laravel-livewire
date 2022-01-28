<?php

namespace Tests\Unit;

use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleModelTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function schedule_can_be_created_using_factory()
    {
        $user = $this->getUser('User');
        $user->schedules()->save(
            Schedule::factory()->make()
        );

        $this->assertDatabaseHas('schedules', [
            'id' => 1,
        ]);
    }

    /** @test */
    public function a_user_can_be_feched_by_the_schedule_instance()
    {
        $user = $this->getUser('User');
        $user->schedules()->save(
            Schedule::factory()->make()
        );

        $schedule = Schedule::first();

        $this->assertTrue($schedule->user->is($user));
    }
}
