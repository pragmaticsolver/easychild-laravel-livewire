<?php

namespace Tests\Feature\Group;

use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class GroupsIndexTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function normal_user_cannot_view_groups_index_page()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('User');
        $this->be($user);

        $this->get(route('groups.index'))
            ->assertRedirect(route('login'));

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function principal_user_cannot_view_groups_index_page()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Principal');

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function manager_can_view_groups_index_page_of_their_organization()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertSuccessful()
            ->assertSeeLivewire('groups.index');
    }

    /** @test */
    public function normal_user_cannot_spoof_livewire_groups_index_component()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('User');

        $this->be($user);

        Livewire::test('groups.index')
            ->assertForbidden();
    }

    /** @test */
    public function principal_user_cannot_spoof_livewire_groups_index_component()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Principal');

        $this->be($user);

        Livewire::test('groups.index')
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_view_groups_list_of_their_organization_only()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        $this->be($user);

        $response = Livewire::test('groups.index');

        $this->assertEquals(1, $response->viewData('groups')->count());

        $this->assertSame($response->viewData('groups')->first()->id, $group->id);
    }

    /** @test */
    public function admins_cannot_view_groups_index_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function test_groups_paginated_list()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        Group::factory()
            ->count(105)
            ->create([
                'organization_id' => $org->id,
            ]);

        $groups = Group::all();

        list($total, $extra) = $this->getPagesCount($groups);

        $this->be($user);

        for ($count = 1; $count <= $total; $count++) {
            $response = Livewire::test('groups.index')
                ->set('page', $count);
            $this->assertEquals(config('setting.perPage'), $response->viewData('groups')->count());
        }

        $response = Livewire::test('groups.index')
            ->set('page', $total + 1);
        $this->assertEquals($extra, $response->viewData('groups')->count());
    }

    /** @test */
    public function groups_index_page_search_return_exact_data()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        $notInSearchGroups = Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $org->id,
            ]);

        $searchName = 'sldkfjlskdf';
        $inSearchGroups = Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $org->id,
                'name' => $searchName,
            ]);

        $this->be($user);

        $totalGroups = $inSearchGroups->count() + $notInSearchGroups->count();
        $response = Livewire::test('groups.index');
        $this->assertEquals(
            $totalGroups,
            $response->viewData('groups')->count()
        );

        $response = Livewire::test('groups.index')
            ->set('search', $searchName);
        $this->assertEquals(
            $inSearchGroups->count(),
            $response->viewData('groups')->count()
        );

        $response = Livewire::test('groups.index', ['search' => $searchName]);
        $this->assertEquals(
            $inSearchGroups->count(),
            $response->viewData('groups')->count()
        );
    }

    /** @test */
    public function groups_index_page_with_search_return_exact_data()
    {
        list($org, $group, $user) = $this->getOrgGroupUser('Manager');

        Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $org->id,
            ]);

        $searchName = 'sldkfjlskdf';
        $inSearchGroups = Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $org->id,
                'name' => $searchName,
            ]);

        $this->be($user);

        $array = [];
        for ($x = 0; $x < $inSearchGroups->count(); $x++) {
            $array[] = $searchName;
        }

        $this->json('GET', route('groups.index'), ['search' => $searchName])
            ->assertSeeTextInOrder($array);
    }
}
