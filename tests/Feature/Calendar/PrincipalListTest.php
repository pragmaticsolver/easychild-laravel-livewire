<?php

namespace Tests\Feature\Calendar;

use App\Models\Group;
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

        $this->extraGroup = Group::factory()->create(['organization_id' => $this->org1->id]);

        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();
        $this->manager2 = $this->getUser('Manager', $this->org2->id);
        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->group2->users()->attach($this->principal2);

        $this->eventTitle = 'Event title 1';

        $this->be($this->principal1);
    }

    /** @test */
    public function principal_wont_see_event_from_other_organization()
    {
        $this->createCalendarEvent($this->eventTitle, $this->manager2);

        $datesArray = [];

        foreach ([0, 1, 2, 3, 4] as $dd) {
            $date = now()->addDays($dd);
            $datesArray[] = trans('calendar-events.main_card_week_day_title', [
                'day' => $date->dayName,
                'date' => $date->format('d'),
                'month' => $date->format('m'),
            ]);
        }

        $this->get(route('calendar'))
            ->assertSeeInOrder([
                trans('calendar-events.page_title'),
                trans('calendar-events.page_week_no', [
                    'week' => now()->weekOfYear,
                ]),
                ...$datesArray,
            ])
            ->assertDontSeeText($this->eventTitle);
    }

    /** @test */
    public function principal_will_see_event_for_their_org()
    {
        $this->createCalendarEvent($this->eventTitle, $this->manager1);

        $this->get(route('calendar'))
            ->assertSeeText($this->eventTitle);
    }

    /** @test */
    public function principal_will_only_see_event_from_their_group_of_their_org()
    {
        $this->createCalendarEvent($this->eventTitle, $this->manager1, [$this->group1->id]);

        $this->get(route('calendar'))
            ->assertSeeText($this->eventTitle);
    }

    /** @test */
    public function principal_will_not_see_event_not_in_their_own_group_of_their_org()
    {
        $this->createCalendarEvent($this->eventTitle, $this->manager1, [$this->extraGroup->id]);

        $this->get(route('calendar'))
            ->assertDontSeeText($this->eventTitle);
    }
}
