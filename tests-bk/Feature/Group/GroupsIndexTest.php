<?php

namespace Tests\Feature\Group;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class GroupsIndexTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    private $org = null;
    private $group = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create();
        $this->group = Group::factory()->create([
            'organization_id' => $this->org->id,
        ]);
    }

    /** @test */
    public function normal_user_cannot_view_groups_index_page()
    {
        $user = $this->getUser('User', $this->org->id);

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_and_manager_only'));
    }

    /** @test */
    public function principal_user_cannot_view_groups_index_page()
    {
        $user = $this->getUser('Principal', $this->org->id);

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
        $user = $this->getUser('User', $this->org->id);

        $this->be($user);

        Livewire::test('groups.index')
            ->assertForbidden();
    }

    /** @test */
    public function principal_user_cannot_spoof_livewire_groups_index_component()
    {
        $user = $this->getUser('Principal', $this->org->id);

        $this->be($user);

        Livewire::test('groups.index')
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_view_groups_list_of_their_organization_only()
    {
        $user = $this->getUser('Manager', $this->org->id);
        $this->group->users()->attach($user->id);

        $this->be($user);

        $response = Livewire::test('groups.index');

        $this->assertEquals(1, $response->viewData('groups')->count());

        $this->assertSame($response->viewData('groups')->first()->id, $this->group->id);
    }

    /** @test */
    public function admins_can_view_groups_index_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertSuccessful()
            ->assertSeeLivewire('groups.index');
    }

    /** @test */
    public function admins_can_view_groups_index_page_with_empty_message()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        // Delete existing group to make empty groups table
        DB::table('groups')->truncate();

        $this->get(route('groups.index'))
            ->assertSuccessful()
            ->assertSeeText(trans('pagination.not_found', ['type' => trans('groups.title_lower')]));
    }

    /** @test */
    public function admin_can_view_groups_index_page_with_one_item()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertSuccessful()
            ->assertSeeLivewire('groups.index')
            ->assertSeeTextInOrder([
                trans('groups.title'),
                trans('groups.add_new'),
                trans('groups.name'),
                trans('groups.organization'),
                trans('groups.principals'),
                trans('groups.users'),
            ]);

        $response = Livewire::test('groups.index');
        $this->assertEquals(1, $response->viewData('groups')->count());
    }

    /** @test */
    public function admin_can_see_all_groups_list_in_view()
    {
        $groups = Group::factory()
            ->count(9)
            ->create([
                'organization_id' => $this->org->id,
            ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('groups.index'))
            ->assertSuccessful()
            ->assertSeeLivewire('groups.index');

        $response = Livewire::test('groups.index');
        $this->assertEquals(1 + $groups->count(), $response->viewData('groups')->count());
    }

    /** @test */
    public function admin_test_groups_paginated_list()
    {
        $user = $this->getUser('Admin');
        Group::factory()
            ->count(105)
            ->create([
                'organization_id' => $this->org->id,
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
        $user = $this->getUser('Admin');

        $notInSearchGroups = Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $this->org->id,
            ]);

        $searchName = 'sldkfjlskdf';
        $inSearchGroups = Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $this->org->id,
                'name' => $searchName,
            ]);

        $this->be($user);

        $response = Livewire::test('groups.index');
        $this->assertEquals(
            // one group already create on before hook
            1 + $inSearchGroups->count() + $notInSearchGroups->count(),
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
        $user = $this->getUser('Admin');

        Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $this->org->id,
            ]);

        $searchName = 'sldkfjlskdf';
        $inSearchGroups = Group::factory()
            ->count(5)
            ->create([
                'organization_id' => $this->org->id,
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
