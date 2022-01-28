<?php

namespace Tests\Feature\User\Create;

use App\Actions\User\NewParentCreateAction;
use App\Actions\User\NewPrincipalCreatedAction;
use App\Models\Contract;
use App\Models\ParentLink;
use App\Models\User;
use App\Notifications\NewUserCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class CreatingTest extends TestCase
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
    }

    /** @test */
    public function admin_creating_admin_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = User::factory()->make([
            'role' => 'Admin',
            'organization_id' => null,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('email', $user->email)
            ->set('role', $user->role)
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('email', $user->email)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewUserCreated::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
            'user_ids' => "[{$user->id}]",
        ]);
    }

    /** @test */
    public function admin_creating_manager_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = User::factory()->make([
            'role' => 'Manager',
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('email', $user->email)
            ->set('role', $user->role)
            ->set('organization_id', $user->organization_id)
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('email', $user->email)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'organization_id' => $user->organization_id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewUserCreated::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
            'user_ids' => "[{$user->id}]",
        ]);
    }

    /** @test */
    public function admin_creating_principal_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = User::factory()->make([
            'role' => 'Principal',
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            // ->set('email', $user->email)
            ->set('role', $user->role)
            ->set('organization_id', $user->organization_id)
            ->set('groups_id', [$this->group->id])
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('given_names', $user->given_names)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'organization_id' => $user->organization_id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewPrincipalCreatedAction::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
        ]);
    }

    /** @test */
    public function admin_creating_vendor_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = User::factory()->make([
            'role' => 'Vendor',
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('email', $user->email)
            ->set('role', $user->role)
            ->set('organization_id', $user->organization_id)
            ->set('vendor_view', 'all')
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('email', $user->email)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'organization_id' => $user->organization_id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewUserCreated::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
            'user_ids' => "[{$user->id}]",
        ]);
    }

    /** @test */
    public function admin_creating_user_role_without_parent_email()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = User::factory()->make([
            'role' => 'User',
            'organization_id' => $this->org->id,
        ]);

        $contract = Contract::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        $parentEmail = 'parent@parent.com';

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('email', 'wow-email@emai.com') // just a test
            ->set('role', $user->role)
            ->set('organization_id', $user->organization_id)
            ->set('group_id', $this->group->id)
            ->set('contract_id', $contract->id)
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('given_names', $user->given_names)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'email' => null,
            'organization_id' => $user->organization_id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('parent_links', [
            'email' => $parentEmail,
            'child_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('custom_jobs', [
            'related_type' => ParentLink::class,
            'action' => NewParentCreateAction::class,
            'due_at' => now()->addMinutes(5),
            'data' => '[]',
            'user_ids' => "[]",
        ]);
    }

    /** @test */
    public function admin_creating_user_role_with_parent_email()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->admin);

        $user = User::factory()->make([
            'role' => 'User',
            'organization_id' => $this->org->id,
        ]);

        $contract = Contract::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        $parentEmail = 'parent@parent.com';

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('role', $user->role)
            ->set('organization_id', $user->organization_id)
            ->set('group_id', $this->group->id)
            ->set('contract_id', $contract->id)
            ->set('parent_email', $parentEmail)
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('given_names', $user->given_names)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'organization_id' => $user->organization_id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('parent_links', [
            'email' => $parentEmail,
            'child_id' => $user->id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => ParentLink::class,
            'action' => NewParentCreateAction::class,
            'due_at' => now()->addMinutes(5),
            'data' => '[]',
            'user_ids' => "[]",
        ]);
    }

    /** @test */
    public function manager_creating_manager_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = User::factory()->make([
            'role' => 'Manager',
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('email', $user->email)
            ->set('role', $user->role)
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('email', $user->email)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'organization_id' => $this->org->id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewUserCreated::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
            'user_ids' => "[{$user->id}]",
        ]);
    }

    /** @test */
    public function manager_creating_principal_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = User::factory()->make([
            'role' => 'Principal',
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('role', $user->role)
            ->set('groups_id', [$this->group->id])
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $user = User::query()
            ->where('given_names', $user->given_names)
            ->first();

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'organization_id' => $this->org->id,
        ]);

        $this->assertDatabaseHas('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewPrincipalCreatedAction::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
            'user_ids' => "[]",
        ]);
    }

    /** @test */
    public function manager_creating_vendor_user()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = User::factory()->make([
            'role' => 'Vendor',
            'organization_id' => $this->org->id,
        ]);

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('email', $user->email)
            ->set('role', $user->role)
            ->set('vendor_view', 'all')
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role,
            'organization_id' => $this->org->id,
        ]);

        $user = User::query()
            ->where('email', $user->email)
            ->first();

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => NewUserCreated::class,
            'due_at' => now()->addMinutes(2),
            'data' => '[]',
            'user_ids' => "[{$user->id}]",
        ]);
    }

    /** @test */
    public function manager_creating_user_role()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->actingAs($this->manager);

        $user = User::factory()->make([
            'role' => 'User',
            'organization_id' => $this->org->id,
        ]);

        $contract = Contract::factory()->create([
            'organization_id' => $this->org->id,
        ]);

        $parentEmail = 'parent@parent.com';

        Livewire::test('users.create')
            ->set('given_names', $user->given_names)
            ->set('last_name', $user->last_name)
            ->set('role', $user->role)
            ->set('group_id', $this->group->id)
            ->set('contract_id', $contract->id)
            ->set('parent_email', $parentEmail)
            ->call('addUser')
            ->assertHasNoErrors()
            ->assertSuccessful()
            ->assertRedirect()
            ->assertSessionHas('success', trans('users.create_success', ['name' => $user->full_name]));

        $this->assertDatabaseHas('users', [
            'given_names' => $user->given_names,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'organization_id' => $this->org->id,
        ]);

        $user = User::query()
            ->where('given_names', $user->given_names)
            ->first();

        $this->assertDatabaseHas('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('parent_links', [
            'email' => $parentEmail,
            'child_id' => $user->id,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'related_type' => ParentLink::class,
            'action' => NewParentCreateAction::class,
            'due_at' => now()->addMinutes(5),
            'data' => '[]',
            'user_ids' => "[]",
        ]);
    }
}
