<?php

namespace Tests\Feature\Message;

use App\Models\Conversation;
use App\Notifications\UserEventsNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SendMessageLivewireTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        // update se
        $settings = [
            'mail' => true,
        ];

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser('User');
        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser('User');

        $this->manager = $this->getUser('Manager', $this->org->id);

        $this->principal = $this->getUser('Principal', $this->org->id, $settings);
        $this->group->users()->attach($this->principal);
    }

    /** @test */
    public function manager_sending_msg_to_users_conversation_will_send_notification_to_all_users()
    {
        Notification::fake();
        $this->be($this->manager);

        $conversation = Conversation::query()
            ->where('organization_id', $this->org->id)
            ->where('group_id', null)
            ->where('participation_id', null)
            ->where('private', false)
            ->where('chat_type', 'users')
            ->first();

        Livewire::test('messages.send', ['id' => $conversation->id])
            ->set('message', 'Test message')
            ->call('sendMessage')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('messages.message_send.sent_success'),
            ]);

        Notification::assertNotSentTo($this->manager, UserEventsNotification::class);
        Notification::assertSentTo(
            $this->principal,
            UserEventsNotification::class,
            function ($notification, $channels, $notifiable) {
                $this->assertTrue(in_array('mail', $channels));

                return $notifiable->uuid == $this->principal->uuid;
            }
        );
        Notification::assertSentTo(
            $this->user,
            UserEventsNotification::class,
            function ($notification, $channels) {
                $this->assertFalse(in_array('mail', $channels));

                return true;
            }
        );
    }

    /** @test */
    public function user_role_sending_msg_to_org_users_convesation_will_send_msg_to_principal_privately()
    {
        Notification::fake();
        $this->be($this->user);

        $conversation = Conversation::query()
            ->where('organization_id', $this->org->id)
            ->where('group_id', null)
            ->where('participation_id', null)
            ->where('private', false)
            ->where('chat_type', 'users')
            ->first();

        $msg = 'This is a test message.';

        Livewire::test('messages.send', ['id' => $conversation->id])
            ->set('message', $msg)
            ->call('sendMessage')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('messages.message_send.sent_success_private'),
            ]);

        $this->assertDatabaseHas('conversations', [
            'organization_id' => $this->org->id,
            'group_id' => null,
            'creator_id' => $this->user->id,
            'private' => true,
            'chat_type' => 'single-group-user',
            'participation_id' => $this->user->userGroup()->id,
        ]);

        Notification::assertSentTo($this->principal, UserEventsNotification::class);
        Notification::assertSentTo($this->manager, UserEventsNotification::class);
        Notification::assertNotSentTo($this->user, UserEventsNotification::class);
    }
}
