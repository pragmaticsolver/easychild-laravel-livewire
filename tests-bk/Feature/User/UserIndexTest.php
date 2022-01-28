<?php

namespace Tests\Feature\User;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserIndexTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function normal_user_cannot_view_user_page()
    {
        $org = Organization::factory()->create();

        $user = $this->getUser('User', $org->id);

        $this->be($user);

        $this->get(route('users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function normal_user_cannot_spoof_user_index_livewire_component()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('User', $org->id);

        $this->be($user);

        Livewire::test('users.index')
            ->assertForbidden();
    }

    /** @test */
    public function principal_cannot_view_users_index_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);

        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function manager_can_view_users_page()
    {
        $org = Organization::factory()->create([
            'name' => 'My First Org',
        ]);

        $user = $this->getUser('Manager', $org->id);
        $this->be($user);

        $this->get(route('users.index'))
            ->assertSeeLivewire('users.index')
            ->assertSuccessful()
            ->assertSeeText(trans('users.principal_title', ['org' => $org->name]));
    }

    /** @test */
    public function admin_can_view_users_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('users.index'))
            ->assertSeeLivewire('users.index')
            ->assertSuccessful()
            ->assertSeeText(trans('users.title'));
    }

    /** @test */
    public function admin_can_see_all_users_list()
    {
        $user = $this->getUser('Admin');
        $users = User::factory()->count(9)->create([
            'organization_id' => null,
        ]);

        $this->be($user);

        $response = Livewire::test('users.index');

        $this->assertEquals(10, $response->viewData('users')->count());
    }

    /** @test */
    public function manager_can_see_all_users_list_in_their_organization()
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $user = $this->getUser('Manager', $org1->id);

        $principalOrgUsers = User::factory()->count(10)->create([
            'organization_id' => $org1->id,
            'role' => 'User',
        ]);

        $otherOrgUsers = User::factory()->count(10)->create([
            'organization_id' => $org2->id,
            'role' => 'User',
        ]);

        $this->be($user);

        $response = Livewire::test('users.index');

        $this->assertEquals(1 + $principalOrgUsers->count(), $response->viewData('users')->count());
    }

    /** @test */
    public function admin_test_users_paginated_list()
    {
        $user = $this->getUser('Admin');
        $users = $this->createUsers(85, [
            'organization_id' => null,
        ]);

        list($total, $extra) = $this->getPagesCount($users);

        $this->be($user);

        for ($count = 1; $count <= $total; $count++) {
            $response = Livewire::test('users.index')
                ->set('page', $count);
            $this->assertEquals(config('setting.perPage'), $response->viewData('users')->count());
        }

        $response = Livewire::test('users.index')
            ->set('page', $total + 1);
        $this->assertEquals($extra + 1, $response->viewData('users')->count());
    }

    /** @test */
    public function users_index_page_search_return_exact_data()
    {
        $user = $this->getUser('Admin');

        $searchName = 'sldkfjlskdf';
        $users = $this->createUsers(10, [
            'given_names' => $searchName,
            'organization_id' => null,
        ]);

        $this->be($user);

        $response = Livewire::test('users.index');
        $this->assertEquals(11, $response->viewData('users')->count());

        $response = Livewire::test('users.index')
            ->set('search', $searchName);
        $this->assertEquals($users->count(), $response->viewData('users')->count());

        $response = Livewire::test('users.index', ['search' => $searchName]);
        $this->assertEquals($users->count(), $response->viewData('users')->count());
    }

    /** @test */
    public function users_index_page_with_search_request_shows_exact_data()
    {
        $user = $this->getUser('Admin');

        $searchName = 'sldkfjlskdf';
        $users = $this->createUsers(5, [
            'given_names' => $searchName,
            'organization_id' => null,
        ]);
        $this->createUsers(5, [
            'organization_id' => null,
        ]);

        $this->be($user);

        $array = [];
        for ($x = 0; $x < $users->count(); $x++) {
            $array[] = $searchName;
        }

        $this->json('GET', route('users.index'), ['search' => $searchName])
            ->assertSeeTextInOrder($array);
    }

    /** @test */
    public function users_index_page_with_request_with_role_query_shows_exact_data()
    {
        $admin = $this->getUser('Admin');
        $this->be($admin);

        $users = $this->createUsers(2, [
            'organization_id' => null,
            'role' => 'User',
        ]);
        $user1 = $users->first();
        $user2 = $users->last();

        $principals = $this->createUsers(2, [
            'organization_id' => null,
            'role' => 'Principal',
        ]);
        $principal1 = $principals->first();
        $principal2 = $principals->last();

        $this->json('GET', route('users.index'), ['role' => 'User'])
            ->assertSeeText($user1->full_name)
            ->assertSeeText($user1->email)
            ->assertSeeText($user2->full_name)
            ->assertSeeText($user2->email)
            ->assertDontSee($principal1->full_name)
            ->assertDontSee($principal1->email)
            ->assertDontSee($principal2->full_name)
            ->assertDontSee($principal2->email);

        $this->json('GET', route('users.index'), ['role' => 'Principal'])
            ->assertDontSee($user1->full_name)
            ->assertDontSee($user1->email)
            ->assertDontSee($user2->full_name)
            ->assertDontSee($user2->email)
            ->assertSeeText($principal1->full_name)
            ->assertSeeText($principal1->email)
            ->assertSeeText($principal2->full_name)
            ->assertSeeText($principal2->email);
    }

    /** @test */
    public function sorting_order_works_when_applied()
    {
        $admin = $this->getUser('Admin');
        $this->be($admin);

        $org = Organization::factory()->create();
        $user1 = User::factory()->create([
            'given_names' => 'Aaaaaa',
            'email' => 'bbbb@aaa.aaa',
            'organization_id' => $org->id,
            'role' => 'User',
        ]);
        $user2 = User::factory()->create([
            'given_names' => 'Bbbbbb',
            'email' => 'aaaa@bbb.bbb',
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        Livewire::test('users.index')
            ->set('role', 'User')
            ->assertSet('sortOrder', 'ASC')
            ->assertSet('sortBy', 'given_names')
            ->assertSeeTextInOrder([
                $user1->full_name,
                $user1->email,
                $user2->full_name,
                $user2->email,
            ]);

        Livewire::test('users.index')
            ->set('role', 'User')
            ->set('sortOrder', 'DESC')
            ->call('changeSort', 'given_names')
            ->assertSeeTextInOrder([
                $user1->full_name,
                $user1->email,
                $user2->full_name,
                $user2->email,
            ]);

        Livewire::test('users.index')
            ->set('role', 'User')
            ->call('changeSort', 'email')
            ->call('changeSort', 'email')
            ->assertSeeTextInOrder([
                $user1->full_name,
                $user1->email,
                $user2->full_name,
                $user2->email,
            ]);
    }

    /** @test */
    public function admin_can_delete_user()
    {
        $admin = $this->getUser('Admin');
        $this->be($admin);

        $org = Organization::factory()->create();
        $user1 = User::factory()->create([
            'given_names' => 'Aaaaaa',
            'email' => 'bbbb@aaa.aaa',
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        Livewire::test('users.index')
            ->call('deleteUser', $user1->uuid)
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.delete_success', [
                    'name' => $user1->full_name,
                ]),
            ]);
    }
}
