<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleManagerTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function manager_cannot_view_schedule_index_for_other_organization()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $user = $this->getUser('User', $org2->id);
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '01:00', '01:30');
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '00:30', '01:30');

        $this->get(route('schedules.type.index', ['organization', $org2->uuid]))
            ->assertForbidden();
    }

    /** @test */
    public function manager_cannot_spoof_schedule_livewire_index_for_other_organization()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $user = $this->getUser('User', $org2->id);
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '01:00', '01:30');
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '00:30', '01:30');

        Livewire::test('schedules.index', ['model' => $org2])
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_see_schedule_when_viewing_from_organization_level()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

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
    public function manager_can_see_schedule_of_group_if_organization_is_same_or_group_is_same()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

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

    /** @test */
    public function principal_wont_see_schedule_for_tomorrow_if_there_are_no_schedules()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id);
        $user2 = $this->getUser('User', $org->id);
        $group->users()->attach($user);
        $group->users()->attach($user2);

        $this->createTestScheduleData($user, now()->format('Y-m-d'), '08:00', '10:00');
        $this->createTestScheduleData($user2, now()->format('Y-m-d'), '11:30', '12:00');

        Livewire::test('schedules.index', ['model' => $group])
            ->assertSeeTextInOrder(['08:00', '09:00', 1])
            ->assertSeeTextInOrder(['09:00', '10:00', 1])
            ->assertSeeTextInOrder(['11:00', '12:00', 1]);

        Livewire::test('schedules.index', ['model' => $group])
            ->call('prevDay')
            ->assertSeeTextInOrder(['08:00', '09:00', 0])
            ->assertSeeTextInOrder(['09:00', '10:00', 0])
            ->assertSeeTextInOrder(['11:00', '12:00', 0]);

        Livewire::test('schedules.index', ['model' => $group])
            ->call('nextDay')
            ->assertSeeTextInOrder(['08:00', '09:00', 0])
            ->assertSeeTextInOrder(['09:00', '10:00', 0])
            ->assertSeeTextInOrder(['11:00', '12:00', 0]);
    }
}
