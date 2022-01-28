<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserModelTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase;

    /** @test */
    public function user_full_name_can_be_retrieve()
    {
        $givenName = 'Santosh';
        $lastName = 'Khanal';
        $fullName = 'Santosh Khanal';

        $user = User::factory()
            ->create([
                'organization_id' => null,
                'given_names' => $givenName,
                'last_name' => $lastName,
                'role' => 'User',
            ]);

        $this->assertEquals($user->full_name, $fullName);
    }

    /** @test */
    public function user_has_organization_relation()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);

        $this->assertTrue($user->organization->is($org));
    }

    /** @test */
    public function user_groups_can_be_retrieve()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id);
        $group->users()->sync([$user->id]);

        $this->assertTrue($user->groups->first()->is($group));
    }

    /** @test */
    public function user_find_by_uuid_fail_exception()
    {
        $user = $this->getUser();

        $foundUser = User::findByUUIDOrFail($user->uuid);
        $this->assertTrue($foundUser->is($user));

        $this->expectException(ModelNotFoundException::class);
        User::findByUUIDOrFail('unknown-uuid');
    }

    /** @test */
    public function can_check_user_role_for_admin()
    {
        $user = $this->getUser('Admin');

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isAdminOrPrincipal());
    }

    /** @test */
    public function can_check_user_role_for_manager()
    {
        $user = $this->getUser('Manager');

        $this->assertTrue($user->isManager());
        $this->assertTrue($user->isAdminOrManager());
    }

    /** @test */
    public function can_check_user_role_for_principal()
    {
        $user = $this->getUser('Principal');

        $this->assertTrue($user->isPrincipal());
        $this->assertTrue($user->isAdminOrPrincipal());
    }
}
