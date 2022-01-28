<?php

namespace Tests\Feature\Calendar;

use App\Models\CalendarEvent;
use App\Models\CustomJob;
use App\Models\Group;
use App\Notifications\CalendarEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OnCalendarEventCreateTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

        // Org 1
        [$this->org1, $this->group1, $this->user1] = $this->getOrgGroupUser();
        $this->manager1 = $this->getUser('Manager', $this->org1->id);
        $this->extraManager1 = $this->getUser('Manager', $this->org1->id);
        $this->principal1 = $this->getUser('Principal', $this->org1->id);
        $this->group1->users()->attach($this->principal1);
        $this->extraGroup1 = Group::factory()->create(['organization_id' => $this->org1->id]);

        // Org 2
        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();
        $this->manager2 = $this->getUser('Manager', $this->org2->id);
        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->extraManager2 = $this->getUser('Manager', $this->org2->id);
        $this->group2->users()->attach($this->principal2);
        $this->extraGroup2 = Group::factory()->create(['organization_id' => $this->org2->id]);

        $this->parent1 = $this->getUser('Parent');
        $this->parent1->childrens()->attach($this->user1);
        $this->parent1->childrens()->attach($this->user2);

        $this->parent2 = $this->getUser('Parent');

        // Org 3
        [$this->org3, $this->group3, $this->user3] = $this->getOrgGroupUser('User');
        $this->manager3 = $this->getUser('Manager', $this->org3->id);
        $this->extraManager3 = $this->getUser('Manager', $this->org3->id);
        $this->principal3 = $this->getUser('Principal', $this->org3->id);
        $this->group3->users()->attach($this->principal3);
        $this->extraGroup3 = Group::factory()->create(['organization_id' => $this->org3->id]);

        $this->event = CalendarEvent::factory()->make();
        $this->fakeFile = 'pdf.pdf';
    }

    private function addEvent($groups = [])
    {
        $fakeFile = UploadedFile::fake()->create($this->fakeFile, 100, 'application/pdf');

        Livewire::test('calendar.create')
            ->set('event.title', $this->event->title)
            ->set('event.description', $this->event->description)
            ->set('event.from', $this->event->from)
            ->set('event.to', $this->event->to)
            ->set('event.all_day', $this->event->all_day)
            ->set('event.color', $this->event->color)
            ->set('files.0', $fakeFile)
            ->set('roles.User', true)
            ->set('roles.Principal', true)
            ->set('roles.Manager', true)
            ->set('groups_id', $groups)
            ->call('submit');
    }

    /** @test */
    public function event_notifies_all_organization_users_when_no_groups_selected()
    {
        $this->be($this->manager1);
        $this->addEvent();

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'files' => '["'.$this->fakeFile.'"]',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
        ]);

        $calendarEvent = CalendarEvent::first();

        $this->assertCount(0, $calendarEvent->groups);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => CalendarEvent::class,
            'action' => CalendarEventNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        // Org 1
        $this->assertContains($this->principal1->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertContains($this->extraManager1->id, $userIds);

        // Org 2
        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->extraManager2->id, $userIds);

        // Org 3
        $this->assertNotContains($this->principal3->id, $userIds);
        $this->assertNotContains($this->manager3->id, $userIds);
        $this->assertNotContains($this->user3->id, $userIds);
        $this->assertNotContains($this->extraManager3->id, $userIds);

        // Parents
        $this->assertContains($this->parent1->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
    }

    /** @test */
    public function event_notifies_all_organization_users_from_single_group_when_no_groups_selected()
    {
        $this->be($this->manager1);
        $groups = [
            $this->group1->id,
        ];

        $this->addEvent($groups);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'files' => '["'.$this->fakeFile.'"]',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
        ]);

        $calendarEvent = CalendarEvent::first();

        $this->assertCount(1, $calendarEvent->groups);
        $this->assertContains($this->group1->id, $calendarEvent->groups);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => CalendarEvent::class,
            'action' => CalendarEventNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        // Org 1
        $this->assertContains($this->principal1->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertContains($this->extraManager1->id, $userIds);

        // Org 2
        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->extraManager2->id, $userIds);

        // Org 3
        $this->assertNotContains($this->principal3->id, $userIds);
        $this->assertNotContains($this->manager3->id, $userIds);
        $this->assertNotContains($this->user3->id, $userIds);
        $this->assertNotContains($this->extraManager3->id, $userIds);

        // Parents
        $this->assertContains($this->parent1->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
    }

    /** @test */
    public function event_notifies_all_organization_users_from_single_extra_group_when_no_groups_selected()
    {
        $this->be($this->manager3);
        $groups = [
            $this->group3->id,
        ];

        $this->addEvent($groups);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'files' => '["'.$this->fakeFile.'"]',
            'creator_id' => $this->manager3->id,
            'organization_id' => $this->org3->id,
        ]);

        $calendarEvent = CalendarEvent::first();

        $this->assertCount(1, $calendarEvent->groups);
        $this->assertContains($this->group3->id, $calendarEvent->groups);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager3->id,
            'related_type' => CalendarEvent::class,
            'action' => CalendarEventNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        // Org 1
        $this->assertNotContains($this->principal1->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->extraManager1->id, $userIds);

        // Org 2
        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->extraManager2->id, $userIds);

        // Org 3
        $this->assertContains($this->principal3->id, $userIds);
        $this->assertNotContains($this->manager3->id, $userIds);
        $this->assertNotContains($this->user3->id, $userIds);
        $this->assertContains($this->extraManager3->id, $userIds);

        // Parents
        $this->assertNotContains($this->parent1->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
    }
}
