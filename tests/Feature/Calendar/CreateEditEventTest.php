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

class CreateEditEventTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp():void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

        // Org 1
        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->extraGroup = Group::factory()->make([
            'name' => 'My First Group',
            'organization_id' => $this->org->id,
        ]);
        $this->extraUser = $this->getUser('User', $this->org->id);
        $this->extraGroup->users()->attach($this->extraUser);
        $this->extraPrincipal = $this->getUser('Principal', $this->org->id);
        $this->extraGroup->users()->attach($this->extraPrincipal);

        $this->manager = $this->getUser('Manager', $this->org->id);
        $this->extraManager = $this->getUser('Manager', $this->org->id);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->group->users()->attach($this->principal);

        $this->parent = $this->getUser('Parent');
        $this->parent->childrens()->attach($this->user);

        // Org 2
        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();
        $this->manager2 = $this->getUser('Manager', $this->org2->id);
        $this->extraManager2 = $this->getUser('Manager', $this->org2->id);
        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->group2->users()->attach($this->principal2);

        $this->parent2 = $this->getUser('Parent');
        $this->parent2->childrens()->attach($this->user2);

        $this->event = CalendarEvent::factory()->make();

        $this->firstUploads = [
            'one.pdf',
            'two.pdf',
        ];

        $this->editUploads = [
            'three.pdf',
            'four.pdf',
        ];
    }

    /** @test */
    public function can_add_new_calendar_event()
    {
        $this->be($this->manager);
        $this->addEvent();

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'creator_id' => $this->manager->id,
            'organization_id' => $this->org->id,
        ]);

        $event = CalendarEvent::first();
        $files = json_decode($event->getRawOriginal('files'));

        $this->assertContains($this->firstUploads[0], $files);
        $this->assertContains($this->firstUploads[1], $files);
    }

    /** @test */
    public function can_edit_calendar_event_with_new_files()
    {
        $this->be($this->manager);
        $this->addEvent();

        $event = CalendarEvent::first();

        $upload1 = UploadedFile::fake()->create($this->editUploads[0], 100, 'application/pdf');
        $upload2 = UploadedFile::fake()->create($this->editUploads[1], 100, 'application/pdf');

        Livewire::test('calendar.create')
            ->call('editEvent', $event->uuid)
            ->set('files.0', $upload1)
            ->set('files.1', $upload2)
            ->call('submit');

        $event->refresh();

        $files = json_decode($event->getRawOriginal('files'));

        $this->assertContains($this->firstUploads[0], $files);
        $this->assertContains($this->firstUploads[1], $files);
        $this->assertContains($this->editUploads[0], $files);
        $this->assertContains($this->editUploads[1], $files);
    }

    /** @test */
    public function can_edit_calendar_event_and_remove_old_file()
    {
        $this->be($this->manager);
        $this->addEvent();

        $event = CalendarEvent::first();

        Livewire::test('calendar.create')
            ->call('editEvent', $event->uuid)
            ->call('removeSingleFile', $this->firstUploads[0])
            ->call('submit');

        $event->refresh();

        $files = json_decode($event->getRawOriginal('files'));

        $this->assertNotContains($this->firstUploads[0], $files);
        $this->assertContains($this->firstUploads[1], $files);
        $this->assertNotContains($this->editUploads[0], $files);
        $this->assertNotContains($this->editUploads[1], $files);
    }

    /** @test */
    public function sends_notification_to_specific_managers()
    {
        $this->be($this->manager);
        $this->addEvent([], ['Manager']);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'creator_id' => $this->manager->id,
            'organization_id' => $this->org->id,
        ]);

        $event = CalendarEvent::first();

        $this->assertDatabaseHas(CustomJob::class, [
            'related_type' => CalendarEvent::class,
            'related_id' => $event->id,
            'action' => CalendarEventNotification::class,
            'due_at' => now()->addMinutes(1),
            'data' => [],
        ]);

        $customJob = CustomJob::first();
        $this->assertNotContains($this->user->id, $customJob->user_ids);
        $this->assertNotContains($this->manager->id, $customJob->user_ids);
        $this->assertContains($this->extraManager->id, $customJob->user_ids);
        $this->assertNotContains($this->principal->id, $customJob->user_ids);
        $this->assertNotContains($this->parent->id, $customJob->user_ids);

        $this->assertNotContains($this->user2->id, $customJob->user_ids);
        $this->assertNotContains($this->manager2->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager2->id, $customJob->user_ids);
        $this->assertNotContains($this->principal2->id, $customJob->user_ids);
        $this->assertNotContains($this->parent2->id, $customJob->user_ids);
    }

    /** @test */
    public function sends_notification_to_specific_principals()
    {
        $this->be($this->manager);
        $this->addEvent([], ['Principal']);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'creator_id' => $this->manager->id,
            'organization_id' => $this->org->id,
        ]);

        $event = CalendarEvent::first();

        $this->assertDatabaseHas(CustomJob::class, [
            'related_type' => CalendarEvent::class,
            'related_id' => $event->id,
            'action' => CalendarEventNotification::class,
            'due_at' => now()->addMinutes(1),
            'data' => [],
        ]);

        $customJob = CustomJob::first();
        $this->assertNotContains($this->user->id, $customJob->user_ids);
        $this->assertNotContains($this->manager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager->id, $customJob->user_ids);
        $this->assertContains($this->principal->id, $customJob->user_ids);
        $this->assertNotContains($this->parent->id, $customJob->user_ids);

        $this->assertNotContains($this->user2->id, $customJob->user_ids);
        $this->assertNotContains($this->manager2->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager2->id, $customJob->user_ids);
        $this->assertNotContains($this->principal2->id, $customJob->user_ids);
        $this->assertNotContains($this->parent2->id, $customJob->user_ids);
    }

    /** @test */
    public function sends_notification_to_specific_principals_with_groups()
    {
        $this->be($this->manager);
        $this->addEvent([$this->group->id], ['Principal']);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'creator_id' => $this->manager->id,
            'organization_id' => $this->org->id,
        ]);

        $event = CalendarEvent::first();

        $this->assertDatabaseHas(CustomJob::class, [
            'related_type' => CalendarEvent::class,
            'related_id' => $event->id,
            'action' => CalendarEventNotification::class,
            'due_at' => now()->addMinutes(1),
            'data' => [],
        ]);

        $customJob = CustomJob::first();
        $this->assertNotContains($this->user->id, $customJob->user_ids);
        $this->assertNotContains($this->manager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraUser->id, $customJob->user_ids);
        $this->assertNotContains($this->extraPrincipal->id, $customJob->user_ids);
        $this->assertContains($this->principal->id, $customJob->user_ids);
        $this->assertNotContains($this->parent->id, $customJob->user_ids);

        $this->assertNotContains($this->user2->id, $customJob->user_ids);
        $this->assertNotContains($this->manager2->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager2->id, $customJob->user_ids);
        $this->assertNotContains($this->principal2->id, $customJob->user_ids);
        $this->assertNotContains($this->parent2->id, $customJob->user_ids);
    }

    /** @test */
    public function sends_notification_to_specific_users_parent()
    {
        $this->be($this->manager);
        $this->addEvent([$this->group->id], ['User']);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'creator_id' => $this->manager->id,
            'organization_id' => $this->org->id,
        ]);

        $event = CalendarEvent::first();

        $this->assertDatabaseHas(CustomJob::class, [
            'related_type' => CalendarEvent::class,
            'related_id' => $event->id,
            'action' => CalendarEventNotification::class,
            'due_at' => now()->addMinutes(1),
            'data' => [],
        ]);

        $customJob = CustomJob::first();
        $this->assertNotContains($this->user->id, $customJob->user_ids);
        $this->assertNotContains($this->manager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraUser->id, $customJob->user_ids);
        $this->assertNotContains($this->extraPrincipal->id, $customJob->user_ids);
        $this->assertNotContains($this->principal->id, $customJob->user_ids);
        $this->assertContains($this->parent->id, $customJob->user_ids);

        $this->assertNotContains($this->user2->id, $customJob->user_ids);
        $this->assertNotContains($this->manager2->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager2->id, $customJob->user_ids);
        $this->assertNotContains($this->principal2->id, $customJob->user_ids);
        $this->assertNotContains($this->parent2->id, $customJob->user_ids);
    }

    /** @test */
    public function sends_notification_to_specific_users_roles_and_groups()
    {
        $this->be($this->manager);
        $this->addEvent([$this->group->id], ['User', 'Principal']);

        $this->assertDatabaseHas('calendar_events', [
            'title' => $this->event->title,
            'description' => $this->event->description,
            'color' => $this->event->color,
            'creator_id' => $this->manager->id,
            'organization_id' => $this->org->id,
        ]);

        $event = CalendarEvent::first();

        $this->assertDatabaseHas(CustomJob::class, [
            'related_type' => CalendarEvent::class,
            'related_id' => $event->id,
            'action' => CalendarEventNotification::class,
            'due_at' => now()->addMinutes(1),
            'data' => [],
        ]);

        $customJob = CustomJob::first();
        $this->assertNotContains($this->user->id, $customJob->user_ids);
        $this->assertNotContains($this->manager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager->id, $customJob->user_ids);
        $this->assertNotContains($this->extraUser->id, $customJob->user_ids);
        $this->assertNotContains($this->extraPrincipal->id, $customJob->user_ids);
        $this->assertContains($this->principal->id, $customJob->user_ids);
        $this->assertContains($this->parent->id, $customJob->user_ids);

        $this->assertNotContains($this->user2->id, $customJob->user_ids);
        $this->assertNotContains($this->manager2->id, $customJob->user_ids);
        $this->assertNotContains($this->extraManager2->id, $customJob->user_ids);
        $this->assertNotContains($this->principal2->id, $customJob->user_ids);
        $this->assertNotContains($this->parent2->id, $customJob->user_ids);
    }
}
