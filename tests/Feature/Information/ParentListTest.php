<?php

namespace Tests\Feature\Information;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ParentListTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

        [$this->org1, $this->group1, $this->user1] = $this->getOrgGroupUser();
        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();

        $this->principal1 = $this->getUser('Principal', $this->org1->id);
        $this->group1->users()->attach($this->principal1);

        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->group2->users()->attach($this->principal2);

        $this->parent = $this->getUser('Parent');
        $this->parent->childrens()->attach($this->user1);
        $this->parent->childrens()->attach($this->user2);

        [$this->org3, $this->group3, $this->principal3] = $this->getOrgGroupUser('Principal');
        $this->manager = $this->getUser('Manager', $this->org3->id);

        $this->infoTitle = 'Information title 1';
        $this->createInformation($this->infoTitle, $this->manager, $this->org3);

        $this->be($this->parent);
    }

    /** @test */
    public function parent_wont_see_information_list_from_other_org()
    {
        $this->get(route('informations.index'))
            ->assertSeeInOrder([
                trans('informations.index_title'),
                trans('informations.title_field'),
                trans('informations.date_of_creation'),
            ])
            ->assertDontSeeText($this->infoTitle);
    }

    /** @test */
    public function parent_will_see_information_available_to_their_child_only()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->principal1, $this->org1);

        $this->get(route('informations.index'))
            ->assertDontSeeText($this->infoTitle)
            ->assertSeeInOrder([
                $title,
                now()->format(config('setting.format.date')),
            ]);
    }

    /** @test */
    public function parent_will_not_see_information_available_only_to_principal()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->principal1, $this->org1, ['Principal']);

        $this->get(route('informations.index'))
            ->assertDontSeeText($this->infoTitle)
            ->assertDontSeeText($title);
    }

    /** @test */
    public function parent_will_not_see_information_available_only_to_manager()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->principal1, $this->org1, ['Manager']);

        $this->get(route('informations.index'))
            ->assertDontSeeText($this->infoTitle)
            ->assertDontSeeText($title);
    }
}
