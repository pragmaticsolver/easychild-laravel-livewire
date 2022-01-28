<?php

namespace Tests\Feature\Presence;

use App\Events\ScheduleUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class PrincipalSchedulePresenceTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->user2 = $this->getUser('User', $this->org->id);

        $this->group->users()->attach($this->user2);
        $this->group->users()->attach($this->principal);

        $this->be($this->principal);
    }

    /** @test */
    public function presence_page_shows_list_of_users()
    {
        $this->schedule = $this->createTestScheduleData($this->user, now());

        $this->get(route('presence'))
            ->assertSeeLivewire('group-class.index')
            ->assertSeeLivewire('group-class.item')
            ->assertSeeInOrder([
                $this->org->name,
                $this->group->name,
                now()->format(config('setting.format.javascript.date')),
                trans('group-class.index_page_total_users', ['number' => 2]),
                trans('group-class.index_page_total_present_users', ['number' => 0]),
                // trans('schedules.prev_btn'),
                // trans('schedules.next_btn'),
                trans('group-class.child_name'),
                trans('group-class.schedule_time'),
                trans('group-class.presence_time'),
                $this->user->full_name,
                $this->group->name,
                trans('group-class.availability.available'),
                $this->user2->full_name,
                trans('group-class.availability.available'),
                // trans('group-class.availability.not-available'),
            ]);
    }

    /** @test */
    public function presence_page_does_not_show_list_of_users_that_are_not_available()
    {
        $this->setUserAvailability($this->user, 'not-available');

        $this->get(route('presence'))
            ->assertSeeLivewire('group-class.index')
            ->assertDontSee($this->user->full_name)
            ->assertSeeInOrder([
                $this->org->name,
                $this->group->name,
                now()->format(config('setting.format.javascript.date')),
                trans('group-class.index_page_total_users', ['number' => 1]),
                trans('group-class.index_page_total_present_users', ['number' => 0]),
                // trans('schedules.prev_btn'),
                // trans('schedules.next_btn'),
                trans('group-class.child_name'),
                trans('group-class.schedule_time'),
                trans('group-class.presence_time'),
                $this->user2->full_name,
                trans('group-class.availability.available'),
            ]);
    }

    /** @test */
    public function presence_single_row_item_presence_time_are_set_correctly()
    {
        $this->schedule = $this->createTestScheduleData($this->user, now());

        Event::fake();
        TestTime::addHour(10);

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule->refresh(),
        ])
            ->assertSet('presenceStart', null)
            ->assertSet('presenceEnd', null);

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule->refresh(),
        ])
            ->call('setPresenceStart')
            ->assertSet('presenceStart', '10:00')
            ->assertSet('presenceEnd', null)
            ->assertEmitted('userPresenceUpdated');

        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->schedule->id == $this->schedule->id;
        });
        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->options['type'] == 'enter';
        });
        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->options['trigger_type'] == 'user';
        });
        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->options['triggred_id'] == $this->principal->id;
        });

        TestTime::addHour()->addMinutes(30);

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule->refresh(),
        ])
            ->call('setPresenceEnd')
            ->assertSet('presenceStart', '10:00')
            ->assertSet('presenceEnd', '11:30')
            ->assertEmitted('userPresenceUpdated');

        Event::assertDispatched(ScheduleUpdated::class, 2);

        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->schedule->id == $this->schedule->id;
        });
        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->options['type'] == 'leave';
        });
        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->options['trigger_type'] == 'user';
        });
        Event::assertDispatched(ScheduleUpdated::class, function ($event) {
            return $event->options['triggred_id'] == $this->principal->id;
        });
    }
}
