<?php

namespace Tests\Feature\Group;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class GroupEditTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_role_cannot_view_group_edit_page()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('User');
        $this->be($user);

        $this->get(route('groups.edit', $group->uuid))
            ->assertRedirect(route('login'));

        $this->assertFalse($this->isAuthenticated());

        // ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function user_role_cannot_spoof_group_edit_livewire_component()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('User');
        $this->be($user);

        Livewire::test('groups.edit', compact('group'))
            ->assertForbidden();
    }

    /** @test */
    public function principal_role_cannot_view_group_edit_page()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Principal');
        $this->be($user);

        $this->get(route('groups.edit', $group->uuid))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function principal_role_cannot_spoof_group_edit_livewire_component()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Principal');
        $this->be($user);

        Livewire::test('groups.edit', compact('group'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_edit_group_within_his_organization()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');
        $this->be($user);

        $this->get(route('groups.edit', $group->uuid))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors()
            ->assertSeeLivewire('groups.edit')
            ->assertSeeTextInOrder([
                $group->name,
                trans('groups.name'),
                trans('groups.organization'),
                trans('groups.update'),
            ]);
    }

    /** @test */
    public function name_is_required_when_editing_group()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('groups.edit', compact('group'))
            ->set('name', null)
            ->call('updateGroup')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function organization_is_required_when_editing_group()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('groups.edit', compact('group'))
            ->set('organization_id', null)
            ->call('updateGroup')
            ->assertHasErrors(['organization_id' => 'required']);
    }

    /** @test */
    public function organization_should_be_managers_organization_when_creating_group_by_manager()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('groups.edit', compact('group'))
            ->set('organization_id', $org2->id)
            ->call('updateGroup')
            ->assertHasErrors('organization_id');
    }

    /** @test */
    public function group_details_is_updating_after_editing()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        $newName = 'Some Different Name';
        Livewire::test('groups.edit', compact('group'))
            ->set('name', $newName)
            ->call('updateGroup')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('groups.update_success'),
            ]);

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => $newName,
            'organization_id' => $org->id,
        ]);
    }
}
