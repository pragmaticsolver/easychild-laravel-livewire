<?php

namespace Tests\Unit\SmsNotificationTest;

use App\Actions\User\AssignParentToChildAction;
use App\CustomNotification\SmsChannel;
use App\Notifications\NewChildLinkedToParentNotification;
use App\Traits\HasConversationParticipants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class NewChildLinkedToParentNotificationTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase, HasConversationParticipants;

    protected function setUp():void
    {
        parent::setUp();

        TestTime::freeze();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
        $this->manager = $this->getUser('Manager', $this->org->id);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->user2 = $this->getUser('User', $this->org->id);
        $this->group->users()->attach($this->principal);

        $this->parent = $this->getUser('Parent');
        $this->parent->childrens()->attach($this->user);

        Notification::fake();
        $this->be($this->manager);

        AssignParentToChildAction::run($this->user2, $this->parent);
    }

    /** @test */
    public function message_notification_sms_not_sent_if_not_enabled_sms_settings()
    {
        $this->parent->update([
            'settings' => [
                'sms' => false,
            ],
        ]);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (NewChildLinkedToParentNotification $newChildLinkedToParentNotification, $channels) {
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

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (NewChildLinkedToParentNotification $newChildLinkedToParentNotification, $channels) {
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

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (NewChildLinkedToParentNotification $newChildLinkedToParentNotification, $channels) {
            $sms = $newChildLinkedToParentNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, url('url'));
            $array[] = Str::contains($sms, trans('users.parent.new-email.child_linked_para', [
                'name' => $this->user2->given_names,
            ]));
            $array[] = Str::contains($sms, $this->org->name);

            return ! in_array(false, $array);
        });
    }
}
