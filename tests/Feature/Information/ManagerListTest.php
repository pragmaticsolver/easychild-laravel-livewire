<?php

namespace Tests\Feature\Information;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ManagerListTest extends TestCase
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

        $this->be($this->manager1);
    }

    /** @test */
    public function manager_wont_see_information_from_other_org()
    {
        $this->createInformation($this->infoTitle, $this->manager2, $this->org2);

        $this->get(route('informations.index'))
            ->assertSeeInOrder([
                trans('informations.index_title'),
                trans('informations.title_field'),
                trans('informations.date_of_creation'),
            ])
            ->assertDontSeeText($this->infoTitle);
    }

    /** @test */
    public function manager_will_see_information_from_their_organization()
    {
        TestTime::freeze();

        $title = 'This information new';
        $this->createInformation($title, $this->principal1, $this->org1);

        $this->get(route('informations.index'))
            ->assertSeeInOrder([
                $title,
                now()->format(config('setting.format.date')),
            ]);
    }
}
