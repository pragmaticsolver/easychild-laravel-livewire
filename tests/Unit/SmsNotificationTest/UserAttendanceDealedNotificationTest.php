<?php

namespace Tests\Unit\SmsNotificationTest;

use App\CustomNotification\SmsChannel;
use App\Models\Schedule;
use App\Notifications\UserAttendanceDealedNotification;
use App\Traits\HasConversationParticipants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserAttendanceDealedNotificationTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase, HasConversationParticipants;

    protected function setUp():void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

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

        $this->schedule = Schedule::create([
            'user_id' => $this->user->id,
            'date' => now()->format('Y-m-d'),
            'eats_onsite' => [
                'breakfast' => true,
                'lunch' => true,
                'dinner' => true,
            ],
            'available' => true,
        ]);
    }

    /** @test */
    public function attendance_approved_sms_not_sent_if_not_enabled_sms_settings()
    {
        $this->schedule->sendDealtNotificationToUser('approved');

        $this->parent->update([
            'settings' => [
                'sms' => false,
            ],
        ]);

        TestTime::addMinutes(5);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (UserAttendanceDealedNotification $userAttendanceDealedNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function attendance_approved_sms_not_sent_if_phone_number_not_saved()
    {
        $this->schedule->sendDealtNotificationToUser('approved');

        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '',
            ],
        ]);

        TestTime::addMinutes(5);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (UserAttendanceDealedNotification $userAttendanceDealedNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function attendance_approved_sms_sent_if_phone_number_saved()
    {
        $this->schedule->sendDealtNotificationToUser('approved');

        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '12345678',
            ],
        ]);

        TestTime::addMinutes(5);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (UserAttendanceDealedNotification $userAttendanceDealedNotification, $channels) {
            $sms = $userAttendanceDealedNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, trans('schedules.notification.user_schedule_approved_title'));
            $array[] = Str::contains($sms, trans('schedules.notification.user_schedule_approved_msg_for_parent', [
                'child' => $this->user->given_names,
                'name' => $this->manager->given_names,
            ]));
            $array[] = Str::contains($sms, $userAttendanceDealedNotification->schedule->approval_description);

            return ! in_array(false, $array);
        });
    }

    /** @test */
    public function attendance_rejected_sms_sent_if_phone_number_saved()
    {
        $this->schedule->sendDealtNotificationToUser('declined');

        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '12345678',
            ],
        ]);

        TestTime::addMinutes(5);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (UserAttendanceDealedNotification $userAttendanceDealedNotification, $channels) {
            $sms = $userAttendanceDealedNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, trans('schedules.notification.user_schedule_rejected_title'));
            $array[] = Str::contains($sms, trans('schedules.notification.user_schedule_rejected_msg_for_parent', [
                'child' => $this->user->given_names,
                'name' => $this->manager->given_names,
            ]));
            $array[] = Str::contains($sms, $userAttendanceDealedNotification->schedule->approval_description);

            return ! in_array(false, $array);
        });
    }
}
