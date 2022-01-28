<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class GroupModelTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase;

    /** @test */
    public function check_if_group_can_access_organization()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->assertTrue($group->organization->is($org));
    }

    /** @test */
    public function check_if_group_can_access_users()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $users = User::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $usersId = $users->pluck('id')->toArray();

        $group->users()->sync($usersId);

        $this->assertCount(10, $group->users);
    }
}
