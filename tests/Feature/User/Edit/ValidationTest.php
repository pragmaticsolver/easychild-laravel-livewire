<?php

namespace Tests\Feature\User\Edit;

use App\Models\Contract;
use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ValidationTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->child] = $this->getOrgGroupUser('User');
        [$this->org2, $this->group2, $this->child2] = $this->getOrgGroupUser('User');

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
    public function given_names_is_required_when_editing_user()
    {
        $this->be($this->admin);
        $user = $this->child;

        Livewire::test('users.edit.base', compact('user'))
            ->set('given_names', '')
            ->call('updateUser')
            ->assertHasErrors(['given_names' => 'required']);
    }

    /** @test */
    public function last_name_is_required_when_editing_user()
    {
        $this->be($this->admin);

        $user = $this->child;

        Livewire::test('users.edit.base', compact('user'))
            ->set('last_name', '')
            ->call('updateUser')
            ->assertHasErrors(['last_name' => 'required']);
    }

    /** @test */
    public function email_is_not_required_when_editing_user_role()
    {
        $this->be($this->admin);

        $user = $this->child;

        Livewire::test('users.edit.base', compact('user'))
            ->set('email', '')
            ->call('updateUser')
            ->assertHasNoErrors(['email']);
    }

    /** @test */
    public function email_is_not_required_when_editing_principal_role()
    {
        $this->be($this->admin);

        $user = $this->principal;

        Livewire::test('users.edit.base', compact('user'))
            ->set('email', '')
            ->call('updateUser')
            ->assertHasNoErrors(['email']);
    }

    /** @test */
    public function email_is_required_when_editing_manager()
    {
        $this->be($this->admin);

        $user = $this->manager;

        Livewire::test('users.edit.base', compact('user'))
            ->set('email', '')
            ->call('updateUser')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_should_be_valid_email_when_editing_manager()
    {
        $this->be($this->admin);

        $user = $this->manager;

        Livewire::test('users.edit.base', compact('user'))
            ->set('email', 'san')
            ->call('updateUser')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function email_should_be_unique_email_when_editing_manager()
    {
        $this->be($this->admin);

        $user = $this->manager;

        Livewire::test('users.edit.base', compact('user'))
            ->set('email', $this->admin->email)
            ->call('updateUser')
            ->assertHasErrors(['email' => 'unique']);
    }

    /** @test */
    public function role_is_required_when_editing_user()
    {
        $this->be($this->admin);

        $user = $this->manager;

        Livewire::test('users.edit.base', compact('user'))
            ->set('role', '')
            ->call('updateUser')
            ->assertHasErrors(['role' => 'required']);
    }

    /** @test */
    public function role_should_be_in_admin_or_principal_manager_or_user_when_updating_user()
    {
        $this->be($this->admin);

        $user = $this->manager;

        Livewire::test('users.edit.base', compact('user'))
            ->set('role', 'Doctor')
            ->call('updateUser')
            ->assertHasErrors(['role' => 'in']);
    }

    /** @test */
    public function manager_cannot_update_admin_user()
    {
        $this->be($this->manager);

        $user = $this->admin;

        Livewire::test('users.edit.base', compact('user'))
            ->assertForbidden();
    }

    /** @test */
    public function organization_is_required_when_updating_vendor_manager_principal_or_user()
    {
        $this->be($this->admin);

        $usersList = [
            $this->vendor,
            $this->manager,
            $this->principal,
            $this->child,
        ];

        foreach ($usersList as $user) {
            Livewire::test('users.edit.base', compact('user'))
                ->set('organization_id', null)
                ->call('updateUser')
                ->assertHasErrors(['organization_id' => 'required']);
        }
    }

    /** @test */
    public function organization_is_not_required_when_updating_admin_user()
    {
        $this->be($this->admin);

        $user = $this->getUser('Admin');

        Livewire::test('users.edit.base', compact('user'))
            ->set('organization_id', null)
            ->call('updateUser')
            ->assertHasNoErrors('organization_id');
    }

    /** @test */
    public function group_id_is_required_when_updating_user_role()
    {
        $this->be($this->admin);

        $user = $this->child;

        Livewire::test('users.edit.base', compact('user'))
            ->set('group_id', null)
            ->call('updateUser')
            ->assertHasErrors(['group_id' => 'required']);
    }

    /** @test */
    public function group_id_is_required_to_be_from_selected_organization_when_updating_user_role()
    {
        $this->be($this->admin);

        $user = $this->child2;

        Livewire::test('users.edit.base', compact('user'))
            ->set('group_id', $this->group->id)
            ->call('updateUser')
            ->assertHasErrors(['group_id']);
    }

    /** @test */
    public function groups_id_can_be_from_selected_organization_when_updating_user_role()
    {
        $this->be($this->admin);

        $user = $this->child2;

        $group3 = Group::factory()->create([
            'organization_id' => $user->organization_id,
        ]);

        Livewire::test('users.edit.base', compact('user'))
            ->set('organization_id', $this->org->id)
            ->set('group_id', $group3->id)
            ->call('updateUser')
            ->assertHasErrors(['group_id']);
    }

    /** @test */
    public function contract_id_is_required_if_updating_user_role()
    {
        $this->be($this->admin);

        $user = $this->child;

        Livewire::test('users.edit.base', compact('user'))
            ->set('contract_id', null)
            ->call('updateUser')
            ->assertHasErrors(['contract_id' => 'required']);
    }

    /** @test */
    public function contract_id_should_be_from_same_organization_updating_user_role()
    {
        $this->be($this->admin);

        $user = $this->child;
        $contract = Contract::factory()->create([
            'organization_id' => $this->org2->id,
        ]);

        Livewire::test('users.edit.base', compact('user'))
            ->set('contract_id', $contract->id)
            ->call('updateUser')
            ->assertHasErrors(['contract_id']);
    }

    /** @test */
    public function groups_id_should_be_an_array_while_updating_principal()
    {
        $this->be($this->admin);

        $user = $this->principal;

        Livewire::test('users.edit.base', compact('user'))
            ->set('groups_id', null)
            ->call('updateUser')
            ->assertHasErrors(['groups_id']);
    }

    /** @test */
    public function groups_id_is_required_while_creating_principal()
    {
        $this->be($this->admin);

        $user = $this->principal;

        Livewire::test('users.edit.base', compact('user'))
            ->set('groups_id', null)
            ->call('updateUser')
            ->assertHasErrors(['groups_id' => 'required']);
    }

    /** @test */
    public function groups_id_is_required_to_be_from_selected_organization_when_creating_principal_role()
    {
        $this->be($this->admin);

        $newOrg = Organization::factory()->create();
        $user = $this->principal;

        Livewire::test('users.edit.base', compact('user'))
            ->set('organization_id', $newOrg->id)
            ->set('groups_id', [$this->group->id])
            ->call('updateUser')
            ->assertHasErrors(['groups_id']);
    }

    /** @test */
    public function groups_id_can_be_from_selected_organization_when_creating_principal_role()
    {
        $this->be($this->admin);

        $newOrg = Organization::factory()->create();
        $group = Group::factory()->create([
            'organization_id' => $newOrg->id,
        ]);
        $user = $this->principal;

        Livewire::test('users.edit.base', compact('user'))
            ->set('organization_id', $newOrg->id)
            ->set('groups_id', [$group->id])
            ->call('updateUser')
            ->assertHasNoErrors(['groups_id']);
    }

    /** @test */
    public function availability_cannot_be_any_other_value_than_availability_options()
    {
        $this->be($this->admin);

        $user = $this->child;

        Livewire::test('users.edit.base', compact('user'))
            ->set('availability', 'hello')
            ->call('updateUser')
            ->assertHasErrors(['availability' => 'in']);
    }

    /** @test */
    public function availability_can_be_only_from_available_config_availability_options()
    {
        $this->be($this->admin);
        $options = collect(config('setting.userAvailabilityOptions'))->only('value')->all();

        $user = $this->child;
        $livewire = Livewire::test('users.edit.base', compact('user'));

        foreach ($options as $opt) {
            $livewire->set('availability', $opt)
                ->call('updateUser')
                ->assertHasNoErrors(['availability']);
        }
    }

    /** @test */
    public function eats_onsite_can_be_null_or_a_boolean_value()
    {
        $this->be($this->manager);

        $eatsOnsiteItems = ['breakfast', 'lunch', 'dinner'];
        $possibleValues = [null, true, false];
        $impossibleValue = ['abc', 123];

        $user = $this->child;
        $livewire = Livewire::test('users.edit.base', compact('user'));

        foreach ($eatsOnsiteItems as $food) {
            foreach ($possibleValues as $test) {
                $livewire->set('eatsOnsite.'.$food, $test)
                    ->call('updateUser')
                    ->assertHasNoErrors(['eatsOnsite.'.$food]);
            }

            foreach ($impossibleValue as $test) {
                $livewire->set('eatsOnsite.'.$food, $test)
                    ->call('updateUser')
                    ->assertHasErrors(['eatsOnsite.'.$food => 'boolean']);
            }
        }
    }

    /** @test */
    public function vendor_view_is_required_to_be_in_all_or_summary_if_updating_vendor()
    {
        $options = ['all', 'summary'];

        $this->be($this->manager);

        $user = $this->vendor;

        $livewire = Livewire::test('users.edit.base', compact('user'));

        foreach ($options as $test) {
            $livewire->set('vendor_view', $test)
                ->call('updateUser')
                ->assertHasNoErrors(['vendor_view']);
        }
    }
}
