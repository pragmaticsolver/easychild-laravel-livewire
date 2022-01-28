<?php

namespace Tests\Feature\Dashboard;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class DashboardTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function dashboard_is_auth_protected()
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function logged_in_user_can_view_dashboard()
    {
        $user = $this->getUser('User');

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSee('Dashboard');
    }

    /** @test */
    public function admin_can_see_total_organization_count()
    {
        $organizations = Organization::factory()
            ->count(10)
            ->create();

        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeTextInOrder([trans('dashboard.total_org'), $organizations->count()]);
    }

    /** @test */
    public function admin_can_see_users_counts_based_on_roles()
    {
        $organization = Organization::factory()->create();

        $user = $this->getUser('Admin');

        $principals = $this->createUsers(20, [
            'organization_id' => $organization->id,
            'role' => 'Principal',
        ]);
        $users = $this->createUsers(30, [
            'organization_id' => $organization->id,
            'role' => 'User',
        ]);

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                trans('dashboard.users_in_org'),
                trans('dashboard.role_admin'),
                1,
                trans('dashboard.role_principal'),
                $principals->count(),
                trans('dashboard.role_user'),
                $users->count(),
            ]);
    }

    /** @test */
    public function principals_cannot_see_organizations_count()
    {
        $organization = Organization::factory()->create();

        $user = $this->getUser('Principal', $organization->id);
        $group = Group::factory()->create([
            'organization_id' => $organization->id,
        ]);
        $group->users()->attach($user->id);

        $users = $this->createUsers(30, [
            'organization_id' => $organization->id,
            'role' => 'User',
        ]);

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertDontSeeText(trans('dashboard.total_org'));
    }

    /** @test */
    public function manager_can_see_user_and_principal_in_his_org()
    {
        $organizations = Organization::factory()
            ->count(10)
            ->create();
        $user = $this->getUser('Manager', $organizations->first()->id);

        $myOrgPrincipals = $this->createUsers(10, [
            'organization_id' => $organizations->first()->id,
            'role' => 'Principal',
        ]);

        $this->createUsers(10, [
            'organization_id' => $organizations->last()->id,
            'role' => 'Principal',
        ]);

        $myOrgUsers = $this->createUsers(10, [
            'organization_id' => $organizations->first()->id,
            'role' => 'User',
        ]);

        $this->createUsers(10, [
            'organization_id' => $organizations->last()->id,
            'role' => 'User',
        ]);

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                trans('dashboard.manager_users_title'),
                trans('dashboard.role_manager'),
                1,
                trans('dashboard.role_principal'),
                $myOrgPrincipals->count(),
                trans('dashboard.role_user'),
                $myOrgUsers->count(),
            ]);
    }

    /** @test */
    public function manager_can_see_their_org_name_and_address()
    {
        $organization = Organization::factory()->create([
            'name' => 'My New Org',
            'street' => 'Street',
            'house_no' => '1',
            'zip_code' => '12345',
            'city' => 'City Name',
        ]);

        $user = $this->getUser('Manager', $organization->id);

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeText(trans('dashboard.your_org'))
            ->assertSeeText($organization->name)
            ->assertSeeText($organization->address);

        $this->assertEquals('Street 1, 12345 City Name', $organization->address);
    }

    /** @test */
    public function user_can_see_their_org_name_and_address()
    {
        $organization = Organization::factory()->create([
            'name' => 'My New Org',
        ]);

        $user = $this->getUser('User', $organization->id);

        $this->be($user);

        $this->get(route('dashboard'))
            ->assertSuccessful()
            ->assertSeeText(trans('dashboard.your_org'))
            ->assertSeeText($organization->name)
            ->assertSeeText($organization->address);
    }
}
