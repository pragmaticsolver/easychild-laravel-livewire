<?php

namespace Tests\Feature\Calendar;

use App\Models\Group;
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

        $this->extraGroup = Group::factory()->create(['organization_id' => $this->org1->id]);

        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();
        $this->manager2 = $this->getUser('Manager', $this->org2->id);
        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->group2->users()->attach($this->principal2);

        $this->eventTitle = 'Event title 1';

        $this->be($this->manager1);
        $this->createCalendarEvent($this->eventTitle, $this->manager2);
    }

    /** @test */
    public function manager_wont_see_event_from_other_organization()
    {
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
    public function manager_will_see_event_for_their_org()
    {
        $evtTitle = 'Event title 2';
        $this->createCalendarEvent($evtTitle, $this->manager1);

        $this->get(route('calendar'))
            ->assertDontSeeText($this->eventTitle)
            ->assertSeeText($evtTitle);
    }
}
