<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleUserTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_role_cannot_view_schedule_of_any_organization()
    {
        $org = Organization::factory()->create();
        $authUser = $this->getUser('User', $org->id);
        $this->be($authUser);

        $user = $this->getUser('User', $org->id);
        $user2 = $this->getUser('User', $org->id);
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '10:00', '13:00');
        $this->createTestScheduleData($user2, now()->format('Y-m-d'), '12:00', '14:30');

        $this->get(route('schedules.type.index', ['organization', $org->uuid]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', trans('extras.admin_and_manager_only'));
    }

    /** @test */
    public function user_role_cannot_view_schedule_of_any_group()
    {
        $org = Organization::factory()->create();
        $authUser = $this->getUser('User');
        $this->be($authUser);

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
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', trans('extras.admin_and_manager_only'));
    }

    /** @test */
    public function user_role_cannot_spoof_schedule_index_livewire_component()
    {
        $org = Organization::factory()->create();
        $authUser = $this->getUser('User');
        $this->be($authUser);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id);
        $user2 = $this->getUser('User', $org->id);
        $group->users()->attach($user);
        $group->users()->attach($user2);

        $this->createTestScheduleData($user, now()->format('Y-m-d'), '08:00', '10:00');
        $this->createTestScheduleData($user2, now()->format('Y-m-d'), '11:30', '12:00');

        Livewire::test('schedules.index', compact('group'))
            ->assertForbidden();
    }
}
