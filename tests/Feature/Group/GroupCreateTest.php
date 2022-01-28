<?php

namespace Tests\Feature\Group;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class GroupCreateTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_cannot_view_groups_create_page()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();

        $this->be($user);

        $this->get(route('groups.create'))
            ->assertRedirect(route('login'));

        $this->assertFalse($this->isAuthenticated());
        // ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function user_cannot_spoof_groups_create_livewire_component()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();

        $this->be($user);

        Livewire::test('groups.create')
            ->assertForbidden();
    }

    /** @test */
    public function principal_cannot_view_groups_create_page()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Principal');

        $this->be($user);

        $this->get(route('groups.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));

        Livewire::test('groups.create')
            ->assertForbidden();
    }

    /** @test */
    public function admin_cannot_view_groups_create_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('groups.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function name_is_required_when_creating_group()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        $this->be($user);

        Livewire::test('groups.create')
            ->set('name', null)
            ->call('addGroup')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function organization_is_required_when_creating_group()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        $this->be($user);

        Livewire::test('groups.create')
            ->set('name', 'Something')
            ->set('organization_id', null)
            ->call('addGroup')
            ->assertHasErrors(['organization_id' => 'required']);
    }

    /** @test */
    public function organization_should_be_managers_organization_when_creating_group_by_manager()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('groups.create')
            ->set('name', 'Something')
            ->set('organization_id', $org2->id)
            ->call('addGroup')
            ->assertHasErrors('organization_id');
    }

    /** @test */
    public function group_is_persisted_in_database_after_creating()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        $title = 'Some Title';

        Livewire::test('groups.create')
            ->set('name', $title)
            ->call('addGroup')
            ->assertHasNoErrors()
            ->assertSessionHas('success', trans('groups.create_success', ['name' => $title]));

        $this->assertDatabaseHas('groups', [
            'name' => 'Some Title',
            'organization_id' => $org->id,
        ]);
    }
}
