<?php

namespace Tests\Feature\User\Index;

use App\Models\Contract;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ParentLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->child] = $this->getOrgGroupUser('User');
        [$this->org2, $this->group2, $this->child2] = $this->getOrgGroupUser('User');

        $this->admin = $this->getUser('Admin');

        $this->manager = $this->getUser('Manager', $this->org->id);

        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->principal->groups()->attach($this->group);

        $this->vendor = $this->getUser('Vendor', $this->org->id, ['vendor_view' => 'all']);

        $contract = Contract::factory()->create([
            'organization_id' => $this->org->id,
        ]);
        $this->child->update([
            'contract_id' => $contract->id,
        ]);

        $this->parent = $this->getUser('Parent');
        $this->parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
        ]);
        $this->parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child2->id,
            'token' => Str::random(),
        ]);

        $this->parent->childrens()->attach($this->child);
        $this->parent->childrens()->attach($this->child2);

        $this->groupConversation = Conversation::query()
            ->where('group_id', $this->group->id)
            ->where('organization_id', $this->org->id)
            ->first();
        $this->personalConversation = Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->child->full_name,
            'organization_id' => $this->child->organization_id,
            'group_id' => null,
            'creator_id' => $this->child->id,
            'private' => true,
            'chat_type' => 'single-group-user',
            'participation_id' => $this->group->id,
        ]);

        // Message from principal
        Message::create([
            'conversation_id' => $this->groupConversation->id,
            'sender_id' => $this->principal->id,
            'body' => Str::random(random_int(15, 100)),
        ]);
        Message::create([
            'conversation_id' => $this->personalConversation->id,
            'sender_id' => $this->principal->id,
            'body' => Str::random(random_int(15, 100)),
        ]);

        // Message from manager
        Message::create([
            'conversation_id' => $this->groupConversation->id,
            'sender_id' => $this->manager->id,
            'body' => Str::random(random_int(15, 100)),
        ]);
        Message::create([
            'conversation_id' => $this->personalConversation->id,
            'sender_id' => $this->manager->id,
            'body' => Str::random(random_int(15, 100)),
        ]);

        // message from parent
        Message::create([
            'conversation_id' => $this->groupConversation->id,
            'sender_id' => $this->parent->id,
            'body' => Str::random(random_int(15, 100)),
        ]);
        Message::create([
            'conversation_id' => $this->personalConversation->id,
            'sender_id' => $this->parent->id,
            'body' => Str::random(random_int(15, 100)),
        ]);
    }

    /** @test */
    public function can_delete_a_child()
    {
        $this->child->delete();

        $this->assertDatabaseMissing('users', [
            'id' => $this->child->id,
        ]);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->groupConversation->id,
            'sender_id' => $this->parent->id,
        ]);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->personalConversation->id,
            'sender_id' => $this->parent->id,
        ]);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $this->groupConversation->id,
            'sender_id' => $this->manager->id,
        ]);

        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $this->personalConversation->id,
            'sender_id' => $this->manager->id,
        ]);

        $this->assertDatabaseMissing('conversations', [
            'id' => $this->personalConversation->id,
        ]);

        $this->assertDatabaseMissing('parent_child', [
            'child_id' => $this->child->id,
            'parent_id' => $this->parent->id,
        ]);

        $this->assertDatabaseMissing('parent_links', [
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
        ]);

        $this->assertDatabaseMissing('group_user', [
            'group_id' => $this->group->id,
            'user_id' => $this->child->id,
        ]);
    }
}
