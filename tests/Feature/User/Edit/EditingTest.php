<?php

namespace Tests\Feature\User\Edit;

use App\Models\Contract;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class EditingTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->child] = $this->getOrgGroupUser('User');

        $this->admin = $this->getUser('Admin');

        $this->manager = $this->getUser('Manager', $this->org->id);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->principal->groups()->attach($this->group);
        $this->vendor = $this->getUser('Vendor', $this->org->id, ['vendor_view' => 'all']);

        $contract = Contract::factory()->create([
            'organization_id' => $this->org->id,
        ]);
        $this->child->update([
            'contract_id' => $contract->id,
        ]);
    }

    /** @test */
    public function admin_editing_admin_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = $this->getUser('Admin');

        Livewire::test('users.edit.base', compact('user'))
            ->set('given_names', 'Given Names')
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'given_names' => 'Given Names',
        ]);
    }

    /** @test */
    public function admin_editing_manager_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = $this->manager;

        $email = 'newemail@email.com';
        Livewire::test('users.edit.base', compact('user'))
            ->set('email', $email)
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $email,
        ]);
    }

    /** @test */
    public function admin_editing_principal_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = $this->principal;

        $group = Group::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.edit.base', compact('user'))
            ->set('last_name', 'last Names')
            ->set('groups_id', [$group->id])
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_name' => 'last Names',
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function admin_editing_vendor_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = $this->vendor;

        Livewire::test('users.edit.base', compact('user'))
            ->set('vendor_view', 'summary')
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertSame('summary', $user->refresh()->settings['vendor_view']);
    }

    /** @test */
    public function admin_editing_user_role()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = $this->child;

        $group = Group::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.edit.base', compact('user'))
            ->set('group_id', $group->id)
            ->set('last_name', 'last Names')
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_name' => 'last Names',
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function manager_editing_manager_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = $this->getUser('Manager', $this->org->id);

        $email = 'newemail@email.com';
        Livewire::test('users.edit.base', compact('user'))
            ->set('email', $email)
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $email,
        ]);
    }

    /** @test */
    public function manager_editing_principal_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = $this->principal;

        $group = Group::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.edit.base', compact('user'))
            ->set('last_name', 'last Names')
            ->set('groups_id', [$group->id])
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_name' => 'last Names',
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function manager_editing_vendor_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = $this->vendor;

        Livewire::test('users.edit.base', compact('user'))
            ->set('vendor_view', 'summary')
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertSame('summary', $user->refresh()->settings['vendor_view']);
    }

    /** @test */
    public function manager_editing_user_role()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = $this->child;

        $group = Group::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.edit.base', compact('user'))
            ->set('group_id', $group->id)
            ->set('last_name', 'last Names')
            ->call('updateUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_name' => 'last Names',
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);
    }
}
