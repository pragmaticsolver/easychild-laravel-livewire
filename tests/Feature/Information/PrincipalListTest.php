<?php

namespace Tests\Feature\Information;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class PrincipalListTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

        [$this->org1, $this->group1, $this->user1] = $this->getOrgGroupUser();
        $this->manager1 = $this->getUser('Manager', $this->org1->id);
        $this->principal1 = $this->getUser('Principal', $this->org1->id);
        $this->group1->users()->attach($this->principal1);

        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();
        $this->manager2 = $this->getUser('Manager', $this->org2->id);
        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->group2->users()->attach($this->principal2);

        $this->infoTitle = 'Information title 1';
        $this->createInformation($this->infoTitle, $this->manager2, $this->org2);

        $this->be($this->principal1);
    }

    /** @test */
    public function principal_wont_see_information_from_other_org()
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
    public function principal_will_see_information_from_their_organization()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->manager1, $this->org1);

        $this->get(route('informations.index'))
            ->assertDontSeeText($this->infoTitle)
            ->assertSeeInOrder([
                $title,
                now()->format(config('setting.format.date')),
            ]);
    }

    /** @test */
    public function principal_will_not_see_information_available_only_to_user()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->manager1, $this->org1, ['User']);

        $this->get(route('informations.index'))
            ->assertDontSeeText($this->infoTitle)
            ->assertDontSeeText($title);
    }

    /** @test */
    public function principal_will_not_see_information_available_only_to_manager()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->manager1, $this->org1, ['Manager']);

        $this->get(route('informations.index'))
            ->assertDontSeeText($this->infoTitle)
            ->assertDontSeeText($title);
    }
}
