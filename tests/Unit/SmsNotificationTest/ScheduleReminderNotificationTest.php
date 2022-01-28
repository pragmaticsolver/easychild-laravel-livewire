<?php

namespace Tests\Unit\SmsNotificationTest;

use App\CustomNotification\SmsChannel;
use App\Notifications\ScheduleReminderNotification;
use App\Traits\HasConversationParticipants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleReminderNotificationTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase, HasConversationParticipants;

    protected function setUp():void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek()->subDays(2));

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->manager = $this->getUser('Manager', $this->org->id);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->group->users()->attach($this->principal);

        $this->parent = $this->getUser('Parent');
        $this->parent->childrens()->attach($this->user);

        $this->user->update([
            'settings' => [
                'availability' => 'not-available-with-time',
            ],
        ]);

        Notification::fake();
        $this->be($this->manager);
    }

    /** @test */
    public function message_notification_sms_not_sent_if_not_enabled_sms_settings()
    {
        $this->parent->update([
            'settings' => [
                'sms' => false,
            ],
        ]);

        // run custom job
        $this->artisan('remind:schedule');

        Notification::assertSentTo($this->parent, function (ScheduleReminderNotification $scheduleReminderNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function message_notification_sms_not_sent_if_phone_number_not_saved()
    {
        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '',
            ],
        ]);

        // run custom job
        $this->artisan('remind:schedule');

        Notification::assertSentTo($this->parent, function (ScheduleReminderNotification $scheduleReminderNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function message_notification_sms_sent_if_phone_number_saved()
    {
        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '12345678',
            ],
        ]);

        // run custom job
        $this->artisan('remind:schedule');

        Notification::assertSentTo($this->parent, function (ScheduleReminderNotification $scheduleReminderNotification, $channels) {
            $sms = $scheduleReminderNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, trans('schedules.notification.reminders.subject'));
            $array[] = Str::contains($sms, trans('schedules.notification.reminders.mail_line'));
            $array[] = Str::contains($sms, route('schedules.index'));
            $array[] = Str::contains($sms, $this->user->given_names);

            return ! in_array(false, $array);
        });
    }
}
