<?php

namespace Tests\Feature\Schedule;

use App\Events\ScheduleUpdated;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleAutoEndTriggerTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->principal = $this->getUser('Principal', $this->org);
        $this->group->users()->attach($this->principal);

        $this->be($this->principal);

        $this->schedule = $this->createTestScheduleData($this->user, now()->startOfWeek());
    }

    /** @test */
    public function principal_can_make_schedule_presence_start_with_dispatched_event()
    {
        TestTime::freeze(now()->startOfWeek()->addHours(10));

        Event::fake();

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule,
        ])
            ->call('setPresenceStart');

        Event::assertDispatched(ScheduleUpdated::class);

        $this->schedule->refresh();

        $this->assertSame(now()->format('H:i:s'), $this->schedule->presence_start);
        $this->assertSame(null, $this->schedule->presence_end);
    }

    /** @test */
    public function principal_can_make_schedule_presence_start_and_it_logs()
    {
        TestTime::freeze(now()->startOfWeek()->addHours(10));

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule,
        ])
            ->call('setPresenceStart');

        $this->schedule->refresh();

        $this->assertSame(now()->format('H:i:s'), $this->schedule->presence_start);
        $this->assertSame(null, $this->schedule->presence_end);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $this->schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
            'created_at' => now(),
        ]);
    }

    /** @test */
    public function principal_can_make_schedule_presence_end_and_it_logs()
    {
        TestTime::freeze(now()->startOfWeek()->addHours(10));

        $component = Livewire::test('group-class.item', [
            'schedule' => $this->schedule,
        ]);

        $component->call('setPresenceStart');

        TestTime::addHour();

        $component->call('setPresenceEnd');

        $this->schedule->refresh();

        $this->assertSame(now()->format('H:i:s'), $this->schedule->presence_end);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $this->schedule->id,
            'type' => 'leave',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
            'created_at' => now(),
        ]);
    }

    /** @test */
    public function schedule_will_not_auto_end_when_organization_opening_time_has_not_end()
    {
        TestTime::freeze(now()->startOfWeek()->addHours(10));

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule,
        ])->call('setPresenceStart');

        $this->schedule->refresh();

        $this->assertSame(now()->format('H:i:s'), $this->schedule->presence_start);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $this->schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
            'created_at' => now(),
        ]);

        TestTime::addHours(7);

        Artisan::call('children-absent:check');

        $this->assertSame(null, $this->schedule->presence_end);

        $this->assertDatabaseMissing('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $this->schedule->id,
            'type' => 'leave',
            'trigger_type' => 'auto',
            'created_at' => now(),
        ]);
    }

    /** @test */
    public function schedule_will_auto_end_when_organization_opening_time_has_end()
    {
        TestTime::freeze(now()->startOfWeek()->addHours(10));

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule,
        ])->call('setPresenceStart');

        $this->schedule->refresh();

        $this->assertSame(now()->format('H:i:s'), $this->schedule->presence_start);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $this->schedule->id,
            'type' => 'enter',
            'trigger_type' => 'user',
            'triggred_id' => $this->principal->id,
            'created_at' => now(),
        ]);

        TestTime::addHours(9);

        Artisan::call('children-absent:check');

        $this->schedule->refresh();

        $orgSettingOpeningTimes = $this->org->settings['opening_times'];
        $todayOpeningTime = collect($orgSettingOpeningTimes)->where('key', now()->dayOfWeek - 1)->first();

        $dateTime = now()->setTimeFromTimeString($todayOpeningTime['end']);

        $this->assertSame($dateTime->format('H:i:s'), $this->schedule->presence_end);

        $this->assertDatabaseHas('user_logs', [
            'user_id' => $this->user->id,
            'typeable_type' => Schedule::class,
            'typeable_id' => $this->schedule->id,
            'type' => 'leave',
            'trigger_type' => 'auto',
            'triggred_id' => null,
            'created_at' => now(),
        ]);
    }
}
