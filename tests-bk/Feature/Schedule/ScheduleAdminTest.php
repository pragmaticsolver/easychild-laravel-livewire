<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleAdminTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function admin_can_view_schedule_of_any_organization()
    {
        $org = Organization::factory()->create();
        $admin = $this->getUser('Admin');
        $this->be($admin);

        $user = $this->getUser('User', $org->id);
        $user2 = $this->getUser('User', $org->id);
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '10:00', '13:00');
        $this->createTestScheduleData($user2, now()->format('Y-m-d'), '12:00', '14:30');

        $this->get(route('schedules.type.index', ['organization', $org->uuid]))
            ->assertSuccessful()
            ->assertSeeTextInOrder(['10:00', '11:00', 1])
            ->assertSeeTextInOrder(['11:00', '12:00', 1])
            ->assertSeeTextInOrder(['12:00', '13:00', 2])
            ->assertSeeTextInOrder(['13:00', '14:00', 1])
            ->assertSeeTextInOrder(['14:00', '15:00', 1]);
    }

    /** @test */
    public function admin_can_view_schedule_of_any_group()
    {
        $org = Organization::factory()->create();
        $admin = $this->getUser('Admin');
        $this->be($admin);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id);
        $user2 = $this->getUser('User', $org->id);
        $group->users()->attach($user);
        $group->users()->attach($user2);

        $this->createTestScheduleData($user, now()->format('Y-m-d'), '08:00', '10:00');
        $this->createTestScheduleData($user2, now()->format('Y-m-d'), '11:30', '12:00');

        $this->get(route('schedules.type.index', ['group', $group->uuid]))
            ->assertSuccessful()
            ->assertSeeTextInOrder(['08:00', '09:00', 1])
            ->assertSeeTextInOrder(['09:00', '10:00', 1])
            ->assertSeeTextInOrder(['11:00', '12:00', 1]);
    }
}
