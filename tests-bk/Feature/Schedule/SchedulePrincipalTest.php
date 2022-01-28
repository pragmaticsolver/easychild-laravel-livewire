<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SchedulePrincipalTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function principal_cannot_view_schedule_index_for_other_organization()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $principal = $this->getUser('Principal', $org->id);
        $this->be($principal);

        $user = $this->getUser('User', $org2->id);
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '01:00', '01:30');
        $this->createTestScheduleData($user, now()->format('Y-m-d'), '00:30', '01:30');

        $this->get(route('schedules.type.index', ['organization', $org2->uuid]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', trans('extras.admin_and_manager_only'));
    }

    /** @test */
    public function principal_can_view_schedule_index_page()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $principal = $this->getUser('Principal', $org->id);
        $group->users()->sync([$principal->id]);

        $this->be($principal);

        $this->get(route('schedules.index'))
            ->assertSuccessful()
            ->assertSeeLivewire('schedules.approve');
    }
}
