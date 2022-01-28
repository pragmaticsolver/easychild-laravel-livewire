<?php

namespace Tests\Feature\User\Create;

use App\Models\Contract;
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

        $this->admin = $this->getUser('Admin');

        $this->manager = $this->getUser('Manager', $this->org->id);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->principal->groups()->attach($this->group);
    }

    /** @test */
    public function given_names_is_required_when_creating_user()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('given_names', '')
            ->call('addUser')
            ->assertHasErrors(['given_names' => 'required']);
    }

    /** @test */
    public function last_name_is_required_when_creating_user()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('last_name', '')
            ->call('addUser')
            ->assertHasErrors(['last_name' => 'required']);
    }

    /** @test */
    public function email_is_not_required_when_creating_user_or_principal_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('email', '')
            ->call('addUser')
            ->assertHasNoErrors(['email'])
            ->set('role', 'Principal')
            ->set('email', '')
            ->call('addUser')
            ->assertHasNoErrors(['email']);
    }

    /** @test */
    public function email_is_required_when_creating_manager()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Manager')
            ->set('email', '')
            ->call('addUser')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_should_be_valid_email_when_creating_manager()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Manager')
            ->set('email', 'santosh')
            ->call('addUser')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function email_should_be_unique_email_when_creating_manager()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Manager')
            ->set('email', $this->manager->email)
            ->call('addUser')
            ->assertHasErrors(['email' => 'unique']);
    }

    /** @test */
    public function parent_email_is_not_required_if_creating_manager_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Manager')
            ->set('parent_email', '')
            ->call('addUser')
            ->assertHasNoErrors(['parent_email']);
    }

    /** @test */
    public function parent_email_is_not_required_if_creating_user_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('parent_email', '')
            ->call('addUser')
            ->assertHasNoErrors(['parent_email']);
    }

    /** @test */
    public function parent_email_is_required_to_be_valid_email_creating_user_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('parent_email', 'santosh')
            ->call('addUser')
            ->assertHasErrors(['parent_email' => 'email']);
    }

    /** @test */
    public function parent_email_check_validation_does_not_occur_if_not_creating_user_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Manager')
            ->set('parent_email', $this->admin->email)
            ->call('addUser')
            ->assertHasNoErrors(['parent_email']);
    }

    /** @test */
    public function role_is_required_when_creating_user()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', '')
            ->call('addUser')
            ->assertHasErrors(['role' => 'required']);
    }

    /** @test */
    public function role_should_be_in_admin_or_principal_manager_or_user_when_creating_user()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Doctor')
            ->call('addUser')
            ->assertHasErrors(['role' => 'in']);
    }

    /** @test */
    public function manager_cannot_create_admin_user()
    {
        $this->be($this->manager);

        $response = Livewire::test('users.create')
            ->set('role', 'Admin')
            ->call('addUser')
            ->assertHasErrors('role');
    }

    /** @test */
    public function organization_is_required_when_creating_vendor_manager_principal_or_user()
    {
        $this->be($this->admin);

        $livewire = Livewire::test('users.create')
            ->set('organization_id', null);

        $roles = ['Vendor', 'Manager', 'User', 'Principal'];

        foreach ($roles as $role) {
            $livewire->set('role', $role)
                ->call('addUser')
                ->assertHasErrors(['organization_id' => 'required']);
        }
    }

    /** @test */
    public function organization_is_not_required_when_creating_admin_user()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('organization_id', null)
            ->set('role', 'Admin')
            ->call('addUser')
            ->assertHasNoErrors('organization_id');
    }

    /** @test */
    public function group_id_is_required_when_creating_user_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('group_id', null)
            ->call('addUser')
            ->assertHasErrors(['group_id' => 'required']);
    }

    /** @test */
    public function group_id_is_required_to_be_from_selected_organization_when_creating_user_role()
    {
        $this->be($this->admin);

        $newOrg = Organization::factory()->create();

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('organization_id', $newOrg->id)
            ->set('group_id', $this->group->id)
            ->call('addUser')
            ->assertHasErrors(['group_id']);
    }

    /** @test */
    public function groups_id_can_be_from_selected_organization_when_creating_user_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('organization_id', $this->org->id)
            ->set('group_id', [$this->group->id])
            ->call('addUser')
            ->assertHasNoErrors(['group_id']);
    }

    /** @test */
    public function contract_id_is_required_if_creating_user_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->call('addUser')
            ->assertHasErrors(['contract_id' => 'required']);
    }

    /** @test */
    public function contract_id_should_be_from_same_organization_creating_user_role()
    {
        $this->be($this->admin);
        $newOrg = Organization::factory()->create();

        $contract = Contract::factory()->create();

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('organization_id', $newOrg->id)
            ->set('contract_id', $contract->id)
            ->call('addUser')
            ->assertHasErrors(['contract_id']);
    }

    /** @test */
    public function groups_id_is_not_required_if_not_creating_principal()
    {
        $this->be($this->admin);

        $livewire = Livewire::test('users.create');

        $roles = ['Vendor', 'Manager', 'User', 'Admin'];

        foreach ($roles as $role) {
            $livewire->set('role', $role)
                ->call('addUser')
                ->assertHasNoErrors(['groups_id']);
        }
    }

    /** @test */
    public function groups_id_should_be_an_array_while_creating_principal()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Principal')
            ->set('groups_id', '')
            ->call('addUser')
            ->assertHasErrors(['groups_id']);
    }

    /** @test */
    public function groups_id_is_required_while_creating_principal()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Principal')
            ->call('addUser')
            ->assertHasErrors(['groups_id' => 'required']);
    }

    /** @test */
    public function groups_id_is_required_to_be_from_selected_organization_when_creating_principal_role()
    {
        $this->be($this->admin);

        $newOrg = Organization::factory()->create();

        Livewire::test('users.create')
            ->set('role', 'Principal')
            ->set('organization_id', $newOrg->id)
            ->set('groups_id', [$this->group->id])
            ->call('addUser')
            ->assertHasErrors(['groups_id']);
    }

    /** @test */
    public function groups_id_can_be_from_selected_organization_when_creating_principal_role()
    {
        $this->be($this->admin);

        Livewire::test('users.create')
            ->set('role', 'Principal')
            ->set('organization_id', $this->org->id)
            ->set('groups_id', [$this->group->id])
            ->call('addUser')
            ->assertHasNoErrors(['groups_id']);
    }

    /** @test */
    public function availability_cannot_be_any_other_value_than_availability_options()
    {
        $this->be($this->admin);
        $options = collect(config('setting.userAvailabilityOptions'))->only('value')->all();

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('availability', 'hello')
            ->call('addUser')
            ->assertHasErrors(['availability' => 'in']);
    }

    /** @test */
    public function availability_can_be_only_from_available_config_availability_options()
    {
        $this->be($this->admin);
        $options = collect(config('setting.userAvailabilityOptions'))->only('value')->all();

        $livewire = Livewire::test('users.create')
            ->set('role', 'User');

        foreach ($options as $opt) {
            $livewire->set('availability', $opt)
                ->call('addUser')
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

        $livewire = Livewire::test('users.create')
            ->set('role', 'User');

        foreach ($eatsOnsiteItems as $food) {
            foreach ($possibleValues as $test) {
                $livewire->set('eatsOnsite.'.$food, $test)
                    ->call('addUser')
                    ->assertHasNoErrors(['eatsOnsite.'.$food]);
            }

            foreach ($impossibleValue as $test) {
                $livewire->set('eatsOnsite.'.$food, $test)
                    ->call('addUser')
                    ->assertHasErrors(['eatsOnsite.'.$food => 'boolean']);
            }
        }
    }

    /** @test */
    public function vendor_view_is_required_to_be_in_all_or_summary_if_creating_vendor()
    {
        $options = ['all', 'summary'];

        $this->be($this->manager);

        $livewire = Livewire::test('users.create')
            ->set('role', 'Vendor');

        foreach ($options as $test) {
            $livewire->set('vendor_view', $test)
                ->call('addUser')
                ->assertHasNoErrors(['vendor_view']);
        }
    }

    /** @test */
    public function vendor_view_is_not_required_if_creating_any_other_user()
    {
        $this->be($this->manager);

        Livewire::test('users.create')
            ->set('role', 'User')
            ->set('vendor_view', null)
            ->call('addUser')
            ->assertHasNoErrors(['vendor_view']);
    }
}
