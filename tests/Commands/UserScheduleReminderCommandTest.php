<?php

namespace Tests\Commands;

use App\Notifications\ScheduleReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserScheduleReminderCommandTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->startOfWeek());

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();

        $this->parent1 = $this->getUser('Parent');
        $this->user->parents()->attach($this->parent1);

        $this->parent2 = $this->getUser('Parent');
        $this->user->parents()->attach($this->parent2);
    }

    /** @test */
    public function only_parents_of_first_user_is_sent_the_reminder()
    {
        Notification::fake();

        // Org 2
        [$org, $group, $user] = $this->getOrgGroupUser();

        $parent = $this->getUser('Parent');
        $user->parents()->attach($parent);

        $this->setLeadAndSelectionTime($org);
        $this->setUserAvailability($this->user, 'available');
        $this->setUserAvailability($user, 'not-available');
        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertNotSentTo($user, ScheduleReminderNotification::class);
        Notification::assertNotSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertNotSentTo($this->parent2, ScheduleReminderNotification::class);

        Notification::assertSentTo($parent, ScheduleReminderNotification::class);
    }

    /** @test */
    public function no_notification_when_schedule_is_available_by_default_for_user_with_available_settings()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'available');

        $this->artisan('remind:schedule');

        Notification::assertNothingSent();
    }

    /** @test */
    public function no_notification_when_schedule_are_already_planned_for_user_with_not_available_settings()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available');

        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'));
        $this->createTestScheduleData($this->user, now()->addDays(2)->format('Y-m-d'));

        $this->artisan('remind:schedule');

        Notification::assertNothingSent();
    }

    /** @test */
    public function notification_is_sent_when_schedule_are_not_planned_for_user_with_not_available_settings()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available');

        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent2, ScheduleReminderNotification::class);
    }

    /** @test */
    public function notification_is_sent_when_minimum_days_schedule_are_not_planned_for_user_with_not_available_settings()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available');

        // only 1 day planned before hand after the lead time
        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'));

        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent2, ScheduleReminderNotification::class);
    }

    /** @test */
    public function notification_is_not_sent_when_minimum_days_schedule_are_planned_for_user_with_not_available_settings()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available');

        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'));
        $this->createTestScheduleData($this->user, now()->addDays(2)->format('Y-m-d'));

        $this->artisan('remind:schedule');

        Notification::assertNothingSent();
    }

    /** @test */
    public function notification_is_sent_when_minimum_days_schedule_are_missed_for_user_with_not_available_settings()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available');

        // Missing 1 days schedule in between
        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'));
        $this->createTestScheduleData($this->user, now()->addDays(3)->format('Y-m-d'));

        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent2, ScheduleReminderNotification::class);
    }

    /** @test */
    public function notification_is_not_sent_with_different_lead_time_value_and_no_schedule_planned()
    {
        Notification::fake();
        $leadTime = 2;

        $this->setLeadAndSelectionTime($this->org, $leadTime);
        $this->setUserAvailability($this->user, 'not-available');

        // Missing 1 days schedule in between
        $this->createTestScheduleData($this->user, now()->addDays($leadTime)->format('Y-m-d'));
        $this->createTestScheduleData($this->user, now()->addDays($leadTime + 1)->format('Y-m-d'));

        $this->artisan('remind:schedule');

        Notification::assertNothingSent();
    }

    /** @test */
    public function notification_is_sent_with_different_lead_time_value_and_no_schedule_planned()
    {
        Notification::fake();
        $leadTime = 2;

        $this->setLeadAndSelectionTime($this->org, $leadTime);
        $this->setUserAvailability($this->user, 'not-available');

        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent2, ScheduleReminderNotification::class);
    }

    /** @test */
    public function notification_is_sent_when_no_schedule_planned_for_user_with_time_schedule_enabled()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available-with-time');

        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent2, ScheduleReminderNotification::class);
    }

    /** @test */
    public function notification_is_sent_when_schedule_planned_without_time_for_user_with_time_schedule_enabled()
    {
        Notification::fake();

        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available-with-time');

        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'));
        $this->createTestScheduleData($this->user, now()->addDays(2)->format('Y-m-d'));

        $this->artisan('remind:schedule');

        Notification::assertNotSentTo($this->user, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent1, ScheduleReminderNotification::class);
        Notification::assertSentTo($this->parent2, ScheduleReminderNotification::class);
    }

    /** @test */
    public function notification_is_not_sent_when_already_set_absent()
    {
        Notification::fake();
        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available');

        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'), null, null, 'approved', false);
        $this->createTestScheduleData($this->user, now()->addDays(2)->format('Y-m-d'), null, null, 'approved', false);

        $this->artisan('remind:schedule');

        Notification::assertNothingSent();
    }

    /** @test */
    public function notification_is_not_sent_when_already_set_absent_for_user_with_time_settings()
    {
        Notification::fake();
        $this->setLeadAndSelectionTime($this->org);
        $this->setUserAvailability($this->user, 'not-available-with-time');

        $this->createTestScheduleData($this->user, now()->addDays(1)->format('Y-m-d'), null, null, 'approved', false);
        $this->createTestScheduleData($this->user, now()->addDays(2)->format('Y-m-d'), null, null, 'approved', false);

        $this->artisan('remind:schedule');

        Notification::assertNothingSent();
    }
}
