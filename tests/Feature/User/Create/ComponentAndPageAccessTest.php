<?php

namespace Tests\Feature\User\Create;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ComponentAndPageAccessTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_with_user_role_cannot_view_user_create_page()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();

        $this->be($user);

        $this->get(route('users.create'))
            ->assertRedirect(route('login'));

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function principal_role_cannot_view_user_create_page()
    {
        [$org, $group, $user] = $this->getOrgGroupUser('Principal');

        $this->be($user);

        $this->get(route('users.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function manager_can_view_user_create_page()
    {
        [$org, $group, $user] = $this->getOrgGroupUser('Manager');

        $this->be($user);

        $this->get(route('users.create'))
            ->assertSessionHasNoErrors()
            ->assertSeeLivewire('users.create')
            ->assertSeeTextInOrder([
                trans('users.add_new_title'),
                trans('users.role'),
                trans('users.given_name'),
                trans('users.last_name'),
                trans('users.dob'),
                trans('users.customer_no'),
                trans('users.organization'),
                $org->name,
                trans('users.group'),
                trans('users.allergies'),
                trans('users.add'),
            ]);
    }

    /** @test */
    public function user_create_livewire_component_cannot_be_faked_by_user()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();

        $this->be($user);

        Livewire::test('users.create')
            ->assertForbidden();
    }

    /** @test */
    public function user_create_livewire_component_cannot_be_faked_by_principal()
    {
        [$org, $group, $user] = $this->getOrgGroupUser('Principal');

        $this->be($user);

        Livewire::test('users.create')
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_view_user_create_page()
    {
        $admin = $this->getUser('Admin');
        [$org, $group, $user] = $this->getOrgGroupUser('Manager');

        $this->be($admin);

        $this->get(route('users.create'))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors()
            ->assertSeeLivewire('users.create')
            ->assertSeeTextInOrder([
                trans('users.add_new_title'),
                trans('users.role'),
                trans('users.given_name'),
                trans('users.last_name'),
                trans('users.dob'),
                trans('users.customer_no'),
                trans('users.organization'),
                trans('users.group'),
                trans('users.allergies'),
                trans('users.add'),
            ]);
    }
}
