<?php

namespace Tests\Feature\User;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserCreateTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_with_user_role_cannot_view_user_create_page()
    {
        $user = $this->getUser();

        $this->be($user);

        $this->get(route('users.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function principal_role_cannot_view_user_create_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('users.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function manager_can_view_user_create_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        $this->get(route('users.create'))
            ->assertSessionHasNoErrors()
            ->assertSeeLivewire('users.create')
            ->assertSeeTextInOrder([
                trans('users.add_new_title'),
                trans('users.given_name'),
                trans('users.last_name'),
                trans('users.email'),
                trans('users.organization'),
                $org->name,
                trans('users.role'),
                trans('users.group'),
                trans('users.add'),
            ]);
    }

    /** @test */
    public function user_create_livewire_component_cannot_be_faked_by_user()
    {
        $user = $this->getUser('User');

        $this->be($user);

        Livewire::test('users.create')
            ->assertForbidden();
    }

    /** @test */
    public function user_create_livewire_component_cannot_be_faked_by_principal()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);
        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        Livewire::test('users.create')
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_view_user_create_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('users.create'))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors()
            ->assertSeeLivewire('users.create')
            ->assertSeeTextInOrder([
                trans('users.add_new_title'),
                trans('users.given_name'),
                trans('users.last_name'),
                trans('users.email'),
                trans('users.organization'),
                trans('users.role'),
                trans('users.add'),
            ]);
    }

    /** @test */
    public function given_names_is_required_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', '')
            ->call('addUser')
            ->assertHasErrors(['given_names' => 'required']);
    }

    /** @test */
    public function last_name_is_required_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('last_name', '')
            ->call('addUser')
            ->assertHasErrors(['last_name' => 'required']);
    }

    /** @test */
    public function email_is_required_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('email', '')
            ->call('addUser')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_should_be_valid_email_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('email', 'santosh')
            ->call('addUser')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function email_should_be_unique_email_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('email', $user->email)
            ->call('addUser')
            ->assertHasErrors(['email' => 'unique']);
    }

    /** @test */
    public function role_is_required_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('role', '')
            ->call('addUser')
            ->assertHasErrors(['role' => 'required']);
    }

    /** @test */
    public function role_should_be_in_admin_or_principal_or_user_when_creating_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('role', 'Doctor')
            ->call('addUser')
            ->assertHasErrors(['role' => 'in']);
    }

    /** @test */
    public function manager_cannot_create_admin_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        $response = Livewire::test('users.create')
            ->set('role', 'Admin')
            ->call('addUser')
            ->assertHasErrors('role');
    }

    /** @test */
    public function organization_is_required_when_creating_principal_or_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('organization_id', null)
            ->set('role', 'User')
            ->call('addUser')
            ->assertHasErrors('organization_id')
            ->set('role', 'Principal')
            ->call('addUser')
            ->assertHasErrors('organization_id');
    }

    /** @test */
    public function organization_is_not_required_when_creating_admin_user()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('organization_id', null)
            ->set('role', 'Admin')
            ->call('addUser')
            ->assertHasNoErrors('organization_id');
    }

    /** @test */
    public function admin_can_create_admin_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', null)
            ->set('role', 'Admin')
            ->call('addUser')
            ->assertSessionHas('success', trans('users.create_success', ['name' => 'John Doe']));

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'Admin',
            'organization_id' => null,
        ]);
    }

    /** @test */
    public function group_is_required_when_creating_user_role()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();

        Livewire::test('users.create')
            ->set('organization_id', $org->id)
            ->set('group_id', null)
            ->set('role', 'User')
            ->call('addUser')
            ->assertHasErrors('group_id');
    }

    /** @test */
    public function groups_is_required_when_creating_principal_role()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();

        Livewire::test('users.create')
            ->set('organization_id', $org->id)
            ->set('groups_id', [])
            ->set('role', 'Principal')
            ->call('addUser')
            ->assertHasErrors('groups_id');
    }

    /** @test */
    public function group_should_be_in_organization_when_creating_principal_or_user_role()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user = $this->getUser('Admin');
        $this->be($user);

        Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $group2 = Group::factory()->create([
            'organization_id' => $org2->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', $org->id)
            ->set('group_id', $group2->id)
            ->set('role', 'User')
            ->call('addUser')
            ->assertHasErrors('group_id')
            ->set('groups_id', [$group2->id])
            ->set('role', 'Principal')
            ->call('addUser')
            ->assertHasErrors('groups_id');
    }

    /** @test */
    public function admin_can_create_principal_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', $org->id)
            ->set('role', 'Principal')
            ->set('groups_id', [$group])
            ->call('addUser')
            ->assertSessionHas('success', trans('users.create_success', ['name' => 'John Doe']));

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'Principal',
            'organization_id' => $org->id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'user_id' => 2,
            'group_id' => 1,
        ]);
    }

    /** @test */
    public function admin_can_create_manager_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', $org->id)
            ->set('role', 'Manager')
            ->call('addUser')
            ->assertSessionHas('success', trans('users.create_success', ['name' => 'John Doe']));

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'Manager',
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function admin_can_create_role_user()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('organization_id', $org->id)
            ->set('group_id', $group->id)
            ->set('role', 'User')
            ->call('addUser')
            ->assertSessionHas('success', trans('users.create_success', ['name' => 'John Doe']));

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'User',
            'organization_id' => $org->id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'user_id' => 2,
            'group_id' => $group->id,
        ]);
    }

    /** @test */
    public function manager_can_create_role_user_and_have_his_org_added_automatically()
    {
        $org = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        $response = Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('role', 'User')
            ->set('group_id', $group->id)
            ->call('addUser')
            ->assertSessionHas('success', trans('users.create_success', ['name' => 'John Doe']));

        $this->assertDatabaseHas('users', [
            'given_names' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
            'role' => 'User',
            'organization_id' => $org->id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'user_id' => 2,
            'group_id' => $group->id,
        ]);
    }

    /** @test */
    public function manager_cannot_create_role_user_with_other_company_id()
    {
        $org = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('role', 'User')
            ->set('organization_id', $org2->id)
            ->call('addUser')
            ->assertHasErrors('organization_id');
    }

    /** @test */
    public function manager_cannot_create_user_with_role_manager()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', 'John')
            ->set('last_name', 'Doe')
            ->set('email', 'john@doe.com')
            ->set('role', 'Manager')
            ->set('organization_id', $org->id)
            ->call('addUser')
            ->assertHasErrors('role');
    }

    /** @test */
    public function groups_multi_select_event_is_fired_when_organization_id_is_changed()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('users.create')
            ->set('role', 'Principal')
            ->set('organization_id', $org->id)
            ->assertEmitted('users.create.selected-groups.updated')
            ->assertEmitted('users.create.user-groups.updated');
    }

    /** @test */
    public function manager_cannot_create_parent_roles_without_attaching_childrens()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', 'Santosh')
            ->set('last_name', 'Khanal')
            ->set('email', 'santosh@parent.com')
            ->set('role', 'Parent')
            ->call('addUser')
            ->assertHasErrors('childrens_id');
    }

    /** @test */
    public function manager_can_create_parent_roles_after_attaching_childrens()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $childrens = User::factory()->count(2)->create([
            'organization_id' => $org->id,
        ]);

        $this->be($user);

        Livewire::test('users.create')
            ->set('given_names', 'Santosh')
            ->set('last_name', 'Khanal')
            ->set('email', 'santosh@parent.com')
            ->set('role', 'Parent')
            ->set('childrens_id', $childrens->toArray())
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSessionHas('success', trans('users.create_success', ['name' => 'Santosh Khanal']));
    }

    /** @test */
    public function update_value_by_key_listener_is_fired_to_change_value()
    {
        $admin = $this->getUser('Admin');

        $org = Organization::factory()->create();

        $this->be($admin);

        Livewire::test('users.create')
            ->emit('updateValueByKey', ['key' => 'role', 'value' => 'Parent'])
            ->assertSet('role', 'Parent');

        Livewire::test('users.create')
            ->set('role', 'Parent')
            ->set('organization_id', $org->id)
            ->assertEmitted('users.create.organization_id.updated');
    }

    /** @test */
    public function sets_of_users_available_for_parents_children_change_when_updaing_org_id()
    {
        $admin = $this->getUser('Admin');

        $org1 = Organization::factory()->create();

        $user = $this->getUser('Parent', $org1->id);
        $users1 = $this->getUser('User', $org1->id);
        $user->childrens()->sync($users1->pluck('id')->toArray());

        $this->be($admin);

        Livewire::test('users.edit', compact('user'))
            ->set('role', 'Parent')
            ->set('organization_id', $org1->id)
            ->assertSet('childrens_id', [])
            ->assertSet('orgUsers', $org1->users()->whereRole('User')->get()->toArray());
    }
}
