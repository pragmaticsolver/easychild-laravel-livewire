<?php

namespace Tests\Feature\Message;

use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SendMessageModelTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser('User');
        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser('User');

        $this->manager = $this->getUser('Manager', $this->org->id);

        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->group->users()->attach($this->principal);
    }

    /** @test */
    public function user_role_cannot_send_message_to_public_conversation()
    {
        $publicConversations = Conversation::query()
            ->where('private', false)
            ->where('organization_id', $this->org->id)
            ->get();

        foreach ($publicConversations as $con) {
            $status = $this->user->canTalkToConversation($con);

            $this->assertFalse($status);
        }
    }

    /** @test */
    public function principal_role_cannot_send_message_to_managers_or_others_group_conversation()
    {
        $publicConversations = Conversation::query()
            ->where('private', false)
            ->where('organization_id', $this->org->id)
            ->where('chat_type', 'managers')
            ->get();

        foreach ($publicConversations as $con) {
            $status = $this->principal->canTalkToConversation($con);

            $this->assertFalse($status);
        }
    }

    /** @test */
    public function principal_role_can_send_message_to_public_conversation_of_his_organization()
    {
        $publicConversations = Conversation::query()
            ->where('private', false)
            ->where('organization_id', $this->org->id)
            ->whereIn('chat_type', [
                'users',
                'staffs',
                'principals',
            ])
            ->get();

        foreach ($publicConversations as $con) {
            $status = $this->principal->canTalkToConversation($con);

            $this->assertTrue($status);
        }
    }

    /** @test */
    public function principal_role_can_send_message_to_private_user_group_of_his_groups()
    {
        Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user->full_name,
            'organization_id' => $this->org->id,
            'creator_id' => $this->user->id,
            'private' => true,
            'chat_type' => 'single-group-user',
            'participation_id' => $this->group->id,
        ]);

        $privateConversation = Conversation::query()
            ->where('private', true)
            ->where('organization_id', $this->org->id)
            ->where('chat_type', 'single-group-user')
            ->get();

        foreach ($privateConversation as $con) {
            $status = $this->principal->canTalkToConversation($con);

            $this->assertTrue($status);
        }
    }

    /** @test */
    public function principal_role_cannot_send_message_to_private_user_group_outside_of_his_group()
    {
        Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user2->full_name,
            'organization_id' => $this->org2->id,
            'creator_id' => $this->user2->id,
            'private' => true,
            'chat_type' => 'single-group-user',
            'participation_id' => $this->group2->id,
        ]);

        $privateConversation = Conversation::query()
            ->where('private', true)
            ->where('organization_id', $this->org2->id)
            ->where('chat_type', 'single-group-user')
            ->get();

        foreach ($privateConversation as $con) {
            $status = $this->principal->canTalkToConversation($con);

            $this->assertFalse($status);
        }
    }

    /** @test */
    public function manager_role_can_send_message_to_public_conversation_of_his_organization()
    {
        $publicConversations = Conversation::query()
            ->where('private', false)
            ->where('organization_id', $this->org->id)
            ->get();

        foreach ($publicConversations as $con) {
            $status = $this->manager->canTalkToConversation($con);

            $this->assertTrue($status);
        }
    }

    /** @test */
    public function manager_role_can_send_message_to_private_user_group()
    {
        Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user->full_name,
            'organization_id' => $this->org->id,
            'creator_id' => $this->user->id,
            'private' => true,
            'chat_type' => 'single-group-user',
            'participation_id' => $this->group->id,
        ]);

        $privateConversation = Conversation::query()
            ->where('private', true)
            ->where('organization_id', $this->org->id)
            ->where('chat_type', 'single-group-user')
            ->get();

        foreach ($privateConversation as $con) {
            $status = $this->manager->canTalkToConversation($con);

            $this->assertTrue($status);
        }
    }
}
