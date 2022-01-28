<?php

namespace Tests\Unit\SmsNotificationTest;

use App\CustomNotification\SmsChannel;
use App\Models\CalendarEvent;
use App\Notifications\CalendarEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class CalendarEventNotificationTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase;

    protected function setUp():void
    {
        parent::setUp();

        TestTime::freeze();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->manager = $this->getUser('Manager', $this->org->id);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->group->users()->attach($this->principal);

        $this->parent = $this->getUser('Parent');
        $this->parent->childrens()->attach($this->user);

        $this->event = CalendarEvent::factory()->make();

        $this->firstUploads = [
            'one.pdf',
            'two.pdf',
        ];

        $this->editUploads = [
            'three.pdf',
            'four.pdf',
        ];

        Notification::fake();

        $this->be($this->manager);
    }

    /** @test */
    public function calendar_event_notification_sms_not_sent_if_not_enabled_sms_settings()
    {
        $this->parent->update([
            'settings' => [
                'sms' => false,
            ],
        ]);

        $this->addEvent([], ['User']);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (CalendarEventNotification $calendarEventNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function calendar_event_notification_sms_not_sent_if_phone_number_not_saved()
    {
        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '',
            ],
        ]);

        $this->addEvent([], ['User']);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (CalendarEventNotification $calendarEventNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function calendar_event_notification_sms_sent_if_phone_number_saved()
    {
        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '12345678',
            ],
        ]);

        $this->addEvent([], ['User']);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (CalendarEventNotification $calendarEventNotification, $channels) {
            $sms = $calendarEventNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, $this->org->name);
            $array[] = Str::contains($sms, trans('calendar-events.notifications.subject'));
            $array[] = Str::contains($sms, $this->event->title);

            return ! in_array(false, $array);
        });
    }
}
