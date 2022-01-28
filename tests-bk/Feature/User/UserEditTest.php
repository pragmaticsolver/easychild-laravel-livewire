<?php

namespace Tests\Feature\User;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserEditTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_with_user_role_cannot_view_user_edit_page()
    {
        $user = $this->getUser();

        $this->be($user);

        $this->get(route('users.edit', $user->uuid))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function normal_user_cannot_spoof_user_edit_livewire_component()
    {
        $user = $this->getUser();
        $user2 = $this->getUser('Principal');

        $this->be($user);

        Livewire::test('users.edit', ['user' => $user2])
            ->assertForbidden();
    }

    /** @test */
    public function user_with_principal_role_cannot_view_user_edit_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('users.edit', $user->uuid))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function principal_user_cannot_spoof_user_edit_livewire_component()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('Principal', $org->id);

        $user2 = $this->getUser('Principal');

        $this->be($user);

        Livewire::test('users.edit', ['user' => $user2])
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_view_user_edit_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('Manager', $org->id);
        $principal = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('users.edit', $principal->uuid))
            ->assertSessionHasNoErrors()
            ->assertSeeLivewire('users.edit')
            ->assertSeeTextInOrder([
                $principal->full_name,
                trans('users.given_name'),
                trans('users.last_name'),
                trans('users.email'),
                trans('users.organization'),
                $org->name,
                trans('users.role'),
                trans('users.group'),
                trans('users.update'),
            ]);
    }

    /** @test */
    public function admin_can_view_user_edit_page()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $principal = $this->getUser('Principal', $org->id);

        $this->get(route('users.edit', $principal->uuid))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors()
            ->assertSeeLivewire('users.edit')
            ->assertSeeTextInOrder([
                $principal->full_name,
                trans('users.given_name'),
                trans('users.last_name'),
                trans('users.email'),
                trans('users.organization'),
                trans('users.role'),
                trans('users.group'),
                trans('users.update'),
            ]);
    }

    /** @test */
    public function given_names_is_required_when_editing_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);

        Livewire::test('users.edit', compact('user'))
            ->set('given_names', '')
            ->call('updateUser')
            ->assertHasErrors(['given_names' => 'required']);
    }

    /** @test */
    public function last_name_is_required_when_editing_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);

        Livewire::test('users.edit', compact('user'))
            ->set('last_name', '')
            ->call('updateUser')
            ->assertHasErrors(['last_name' => 'required']);
    }

    /** @test */
    public function email_is_required_when_editing_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);

        Livewire::test('users.edit', compact('user'))
            ->set('email', '')
            ->call('updateUser')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_should_be_valid_email_when_editing_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);

        Livewire::test('users.edit', compact('user'))
            ->set('email', 'santosh')
            ->call('updateUser')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function email_should_be_unique_email_when_updating_user()
    {
        $extraUser = $this->getUser('User');
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);

        Livewire::test('users.edit', compact('user'))
            ->set('email', $extraUser->email)
            ->call('updateUser')
            ->assertHasErrors(['email' => 'unique']);
    }

    /** @test */
    public function email_should_be_unique_email_but_it_can_be_same_as_current_when_updating_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);
        $user->groups()->attach($groups->first());

        Livewire::test('users.edit', compact('user'))
            ->set('email', $user->email)
            ->call('updateUser')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);
    }

    /** @test */
    public function role_is_required_when_updating_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);
        $user->groups()->attach($groups->first());

        Livewire::test('users.edit', compact('user'))
            ->set('role', '')
            ->call('updateUser')
            ->assertHasErrors(['role' => 'required']);
    }

    /** @test */
    public function role_should_be_in_admin_or_principal_or_user_when_updating_user()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $groups = Group::factory()->count(10)->create([
            'organization_id' => $org->id,
        ]);
        $manager->groups()->sync($groups->pluck('id')->toArray());

        $this->be($manager);

        $user = $this->getUser('User', $org->id);
        $user->groups()->attach($groups->first());

        Livewire::test('users.edit', compact('user'))
            ->set('role', 'Doctor')
            ->call('updateUser')
            ->assertHasErrors(['role' => 'in']);
    }

    /** @test */
    public function manager_cannot_update_to_admin_role_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('users.edit', compact('user'))
            ->set('role', 'Admin')
            ->call('updateUser')
            ->assertHasErrors('role');
    }

    /** @test */
    public function manager_cannot_update_to_manager_role_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('users.edit', compact('user'))
            ->set('role', 'Manager')
            ->call('updateUser')
            ->assertHasErrors('role');
    }

    /** @test */
    public function organization_is_required_when_updating_principal_user()
    {
        $user = $this->getUser('Admin');

        $org = Organization::factory()->create();
        $updatingUser = $this->getUser('Principal', $org->id);

        $this->be($user);

        Livewire::test('users.edit', ['user' => $updatingUser])
            ->set('organization_id', null)
            ->set('role', 'User')
            ->call('updateUser')
            ->assertHasErrors('organization_id', 'required_if')
            ->set('role', 'Principal')
            ->call('updateUser')
            ->assertHasErrors('organization_id', 'required_if');
    }

    /** @test */
    public function organization_is_not_required_when_updating_admin_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.edit', compact('user'))
            ->set('organization_id', null)
            ->set('role', 'Admin')
            ->call('updateUser')
            ->assertHasNoErrors('organization_id');
    }

    /** @test */
    public function admin_can_update_admin_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.edit', compact('user'))
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', null)
            ->set('role', 'Admin')
            ->call('updateUser')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'Admin',
            'organization_id' => null,
        ]);
    }

    /** @test */
    public function admin_can_update_manager_user()
    {
        $admin = $this->getUser('Admin');

        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user->groups()->sync($group);

        $this->be($admin);

        Livewire::test('users.edit', compact('user'))
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', $org->id)
            ->call('updateUser')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'Manager',
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function admin_can_update_principal_user()
    {
        $admin = $this->getUser('Admin');

        $org = Organization::factory()->create();
        $user = $this->getUser('Principal', $org->id);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user->groups()->sync($group);

        $this->be($admin);

        Livewire::test('users.edit', compact('user'))
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', $org->id)
            ->set('role', 'Principal')
            ->call('updateUser')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'Principal',
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function manager_cannot_update_role_user_with_other_company_id()
    {
        $org = Organization::factory()->create();
        $principal = $this->getUser('Manager', $org->id);
        $user = $this->getUser('User', $org->id);

        $org2 = Organization::factory()->create();

        $this->be($principal);

        Livewire::test('users.edit', compact('user'))
            ->set('organization_id', $org2->id)
            ->call('updateUser')
            ->assertHasErrors('organization_id');
    }

    /** @test */
    public function groups_multi_select_event_is_fired_when_organization_id_is_changed()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        $principal = $this->getUser('Principal', $org->id);
        $org2 = Organization::factory()->create();

        Livewire::test('users.edit', ['user' => $principal])
            ->set('organization_id', $org2->id)
            ->assertEmitted('users.edit.selected-groups.updated')
            ->assertEmitted('users.edit.user-groups.updated');
    }

    /** @test */
    public function admin_can_update_parent_user()
    {
        $admin = $this->getUser('Admin');

        $org = Organization::factory()->create();
        $user = $this->getUser('Parent', $org->id);

        $children = User::factory()->count(2)->create([
            'organization_id' => $org->id,
            'role' => 'User',
        ]);
        $newChildrens = User::factory()->count(3)->create([
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        $user->childrens()->sync($children->pluck('id'));

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->sync($children->pluck('id')->toArray());

        $this->be($admin);

        Livewire::test('users.edit', compact('user'))
            ->set('childrens_id', $newChildrens->toArray())
            ->call('updateUser')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $user = User::find($user->id);
        $this->assertSame($newChildrens->pluck('id')->toArray(), $user->childrens->pluck('id')->toArray());
    }

    /** @test */
    public function manager_can_update_roles_to_parent()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);

        $user = $this->getUser('User', $org->id);
        $newChildrens = User::factory()->count(3)->create([
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->sync($newChildrens->pluck('id')->toArray());

        $this->be($manager);

        Livewire::test('users.edit', compact('user'))
            ->set('role', 'Parent')
            ->set('childrens_id', $newChildrens->toArray())
            ->call('updateUser')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $user = User::find($user->id);
        $this->assertSame($newChildrens->pluck('id')->toArray(), $user->childrens->pluck('id')->toArray());
    }

    /** @test */
    public function update_value_by_key_listener_is_fired_to_change_value()
    {
        $admin = $this->getUser('Admin');

        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group->users()->attach($user->id);

        $this->be($admin);

        Livewire::test('users.edit', compact('user'))
            ->emit('updateValueByKey', ['key' => 'role', 'value' => 'Parent'])
            ->assertSet('role', 'Parent');
    }

    /** @test */
    public function sets_of_users_available_for_parents_children_change_when_updaing_org_id()
    {
        $admin = $this->getUser('Admin');

        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user = $this->getUser('Parent', $org1->id);
        $users1 = $this->getUser('User', $org1->id);
        $user->childrens()->sync($users1->pluck('id')->toArray());

        $users2 = $this->getUser('User', $org2->id);

        $this->be($admin);

        Livewire::test('users.edit', compact('user'))
            ->set('organization_id', $org2->id)
            ->assertSet('childrens_id', [])
            ->assertSet('orgUsers', $org2->users()->whereRole('User')->get()->toArray());
    }
}
