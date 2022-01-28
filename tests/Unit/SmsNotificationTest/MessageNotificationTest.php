<?php

namespace Tests\Unit\SmsNotificationTest;

use App\CustomNotification\SmsChannel;
use App\Notifications\MessageNotification;
use App\Traits\HasConversationParticipants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class MessageNotificationTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase, HasConversationParticipants;

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

        Notification::fake();
        $this->be($this->manager);
    }

    private function sendMessage($message)
    {
        Livewire::test('messages.send')
            ->set('conversationId', 1)
            ->set('message', $message)
            ->call('sendMessage');
    }

    /** @test */
    public function message_notification_sms_not_sent_if_not_enabled_sms_settings()
    {
        $this->parent->update([
            'settings' => [
                'sms' => false,
            ],
        ]);

        $this->sendMessage(Str::random(50));

        TestTime::addMinutes(2);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (MessageNotification $messageNotification, $channels) {
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

        $this->sendMessage(Str::random(50));

        TestTime::addMinutes(2);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (MessageNotification $messageNotification, $channels) {
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

        $msg1 = Str::random(50);
        $msg2 = Str::random(50);
        $this->sendMessage($msg1);
        $this->sendMessage($msg2);

        TestTime::addMinutes(2);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (MessageNotification $messageNotification, $channels) use ($msg1, $msg2) {
            $sms = $messageNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, url('url'));
            $array[] = Str::contains($sms, $this->org->name);
            $array[] = Str::contains($sms, $msg1);
            $array[] = Str::contains($sms, $msg2);

            return ! in_array(false, $array);
        });
    }
}
