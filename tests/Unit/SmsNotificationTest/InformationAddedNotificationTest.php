<?php

namespace Tests\Unit\SmsNotificationTest;

use App\CustomNotification\SmsChannel;
use App\Notifications\InformationAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class InformationAddedNotificationTest extends TestCase
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

        $this->newInfoTitle = 'New Info title 1';

        Notification::fake();

        $this->be($this->manager);
    }

    private function createInformation()
    {
        $fakeFile = UploadedFile::fake()->create('pdf.pdf');

        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            // ->set('description', $this->newInfoTitle)
            ->set('roles.User', true)
            ->set('file', $fakeFile)
            ->call('addInformation');
    }

    /** @test */
    public function information_notification_sms_not_sent_if_not_enabled_sms_settings()
    {
        $this->parent->update([
            'settings' => [
                'sms' => false,
            ],
        ]);

        $this->createInformation();

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (InformationAddedNotification $informationAddedNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function information_notification_sms_not_sent_if_phone_number_not_saved()
    {
        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '',
            ],
        ]);

        $this->createInformation();

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (InformationAddedNotification $informationAddedNotification, $channels) {
            return ! in_array(SmsChannel::class, $channels);
        });
    }

    /** @test */
    public function information_notification_sms_sent_if_phone_number_saved()
    {
        $this->parent->update([
            'settings' => [
                'sms' => true,
                'phone' => '12345678',
            ],
        ]);

        $this->createInformation();

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        Notification::assertSentTo($this->parent, function (InformationAddedNotification $informationAddedNotification, $channels) {
            $sms = $informationAddedNotification->toLxSms($this->parent)['text'];

            $array = [];
            $array[] = in_array(SmsChannel::class, $channels);
            $array[] = Str::contains($sms, $this->org->name);
            $array[] = Str::contains($sms, $this->newInfoTitle);

            return ! in_array(false, $array);
        });
    }
}
