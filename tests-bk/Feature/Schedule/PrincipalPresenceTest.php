<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class PrincipalPresenceTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create([
            'name' => 'My Org Name',
        ]);
        $this->principal = $this->getUser('Principal', $this->org->id);

        $this->group1 = Group::factory()->create([
            'name' => 'My Group 1',
            'organization_id' => $this->org->id,
        ]);
        $this->group2 = Group::factory()->create([
            'name' => 'My Group 2',
            'organization_id' => $this->org->id,
        ]);

        $this->user1 = $this->getUser('User', $this->org->id);
        $this->group1->users()->attach($this->user1);

        $this->user2 = $this->getUser('User', $this->org->id);
        $this->group2->users()->attach($this->user2);

        $this->group1->users()->attach($this->principal);
        $this->group2->users()->attach($this->principal);

        $this->schedule1 = $this->createTestScheduleData($this->user1, now()->format('Y-m-d'), '08:00:00', '10:00:00');
        $this->schedule2 = $this->createTestScheduleData($this->user2, now()->format('Y-m-d'), '08:30:00', '11:00:00');

        $this->be($this->principal);
    }

    /** @test */
    public function principal_can_see_his_default_group_dashboard()
    {
        $scheduleTime = $this->removeSecondsFromTime($this->schedule1->start);
        $scheduleTime .= ' - ';
        $scheduleTime .= $this->removeSecondsFromTime($this->schedule1->end);

        $this->get(route('presence'))
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                $this->org->name,
                $this->group1->name,
                $this->principal->full_name,
                trans('group-class.index_page_total_users', ['number' => 1]),
                trans('group-class.index_page_total_present_users', ['number' => 0]),
                trans('group-class.child_name'),
                trans('group-class.schedule_time'),
                trans('group-class.presence_time'),
                $this->user1->full_name,
                // $scheduleTime,
                // 'N/A - N/A',
            ]);
    }

    /** @test */
    public function test_group_class_single_item_for_schedule_time_visiblility()
    {
        $scheduleTime = $this->removeSecondsFromTime($this->schedule1->start);
        $scheduleTime .= ' - ';
        $scheduleTime .= $this->removeSecondsFromTime($this->schedule1->end);

        $this->schedule1->given_names = $this->user1->given_names;
        $this->schedule1->last_name = $this->user1->last_name;
        $this->schedule1->group_name = $this->group1->name;

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule1,
        ])
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                $this->user1->full_name,
                // $scheduleTime,
                'N/A - N/A',
            ]);
    }

    /** @test */
    public function test_group_class_single_item_for_schedule_presence_start_and_end_change()
    {
        $now = now();
        $scheduleTime = $this->removeSecondsFromTime($this->schedule1->start);
        $scheduleTime .= ' - ';
        $scheduleTime .= $this->removeSecondsFromTime($this->schedule1->end);

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule1,
        ])
            ->call('setPresenceStart')
            ->assertEmitted('userPresenceUpdated')
            ->assertSet('presenceStart', $now->format('H:i'));

        $this->get(route('presence'))
            ->assertSuccessful()
            ->assertSeeText(trans('group-class.index_page_total_present_users', ['number' => 1]));

        Carbon::setTestNow(now()->addMinutes(30));

        Livewire::test('group-class.item', [
            'schedule' => $this->schedule1,
        ])
            ->call('setPresenceEnd')
            ->assertEmitted('userPresenceUpdated')
            ->assertSet('presenceEnd', $now->addMinutes(30)->format('H:i'));

        $this->get(route('presence'))
            ->assertSuccessful()
            ->assertSeeText(trans('group-class.index_page_total_present_users', ['number' => 0]));
    }
}
