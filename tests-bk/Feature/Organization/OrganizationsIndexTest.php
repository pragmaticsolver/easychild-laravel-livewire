<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OrganizationsIndexTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function normal_user_cannot_view_organizations_page()
    {
        $org = Organization::factory()->create();

        $user = $this->getUser('User', $org->id);

        $this->be($user);

        $this->get(route('organizations.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_only'));
    }

    /** @test */
    public function principal_cannot_view_organizations_page()
    {
        $org = Organization::factory()->create();

        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('organizations.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_only'));
    }

    /** @test */
    public function principal_cannot_spoof_organization_index_livewire_component()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        Livewire::test('organizations.index')
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_view_organization_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('organizations.index'))
            ->assertSeeLivewire('organizations.index')
            ->assertSuccessful();
    }

    /** @test */
    public function admin_can_view_organization_page_with_empty_message()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('organizations.index'))
            ->assertSeeLivewire('organizations.index')
            ->assertSuccessful()
            ->assertSeeText(trans('pagination.not_found', ['type' => trans('organizations.title_lower')]));
    }

    /** @test */
    public function admin_can_view_organization_page_with_one_item()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('organizations.index'))
            ->assertSeeLivewire('organizations.index')
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                trans('organizations.title'),
                trans('organizations.add_new'),
                trans('organizations.name'),
                trans('organizations.address'),
                trans('organizations.groups'),
                trans('organizations.users'),
            ]);

        $this->assertDatabaseCount('organizations', 1);

        $response = Livewire::test('organizations.index');
        $this->assertEquals(1, $response->viewData('organizations')->count());
    }

    /** @test */
    public function admin_can_see_all_organization_list_in_view()
    {
        $orgs = Organization::factory()->count(10)->create();

        $user = $this->getUser('Admin');

        $this->be($user);

        $response = Livewire::test('organizations.index');

        $this->assertEquals(10, $response->viewData('organizations')->count());
    }

    /** @test */
    public function admin_test_organizations_paginated_list()
    {
        $user = $this->getUser('Admin');
        $orgs = Organization::factory()->count(105)->create();

        list($total, $extra) = $this->getPagesCount($orgs);

        $this->be($user);

        for ($count = 1; $count <= $total; $count++) {
            $response = Livewire::test('organizations.index')
                ->set('page', $count);
            $this->assertEquals(config('setting.perPage'), $response->viewData('organizations')->count());
        }

        $response = Livewire::test('organizations.index')
            ->set('page', $total + 1);
        $this->assertEquals($extra, $response->viewData('organizations')->count());
    }

    /** @test */
    public function organizations_index_page_search_return_exact_data()
    {
        $user = $this->getUser('Admin');

        $notInSearchOrgs = Organization::factory()->count(5)->create();

        $searchName = 'sldkfjlskdf';
        $inSearchOrgs = Organization::factory()->count(5)->create([
            'name' => $searchName,
        ]);

        $this->be($user);

        $response = Livewire::test('organizations.index');
        $this->assertEquals($inSearchOrgs->count() + $notInSearchOrgs->count(), $response->viewData('organizations')->count());

        $response = Livewire::test('organizations.index')
            ->set('search', $searchName);
        $this->assertEquals($inSearchOrgs->count(), $response->viewData('organizations')->count());

        $response = Livewire::test('organizations.index', ['search' => $searchName]);
        $this->assertEquals($inSearchOrgs->count(), $response->viewData('organizations')->count());
    }

    /** @test */
    public function organizations_index_page_with_search_return_exact_data()
    {
        $user = $this->getUser('Admin');

        Organization::factory()->count(5)->create();

        $searchName = 'sldkfjlskdf';
        $inSearchOrgs = Organization::factory()->count(5)->create([
            'name' => $searchName,
        ]);

        $this->be($user);

        $array = [];
        for ($x = 0; $x < $inSearchOrgs->count(); $x++) {
            $array[] = $searchName;
        }

        $this->json('GET', route('organizations.index'), ['search' => $searchName])
            ->assertSeeTextInOrder($array);
    }
}
