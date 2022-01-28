<?php

namespace Tests\Feature\Message;

use App\Models\Conversation;
use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class CreateConversationTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create([
            'name' => 'My Org Name',
        ]);
        $this->principal = $this->getUser('Principal', $this->org->id);
        $this->manager = $this->getUser('Manager', $this->org->id);

        $this->group1 = Group::factory()->create([
            'name' => 'My Group 1',
            'organization_id' => $this->org->id,
        ]);

        $this->user1 = $this->getUser('User', $this->org->id);

        $this->group1->users()->attach($this->user1);
        $this->group1->users()->attach($this->principal);
    }

    /** @test */
    public function userId_is_required_if_you_are_manager()
    {
        $this->be($this->manager);

        Livewire::test('messages.create')
            ->call('createNewRoom')
            ->assertHasErrors(['userId']);
    }

    /** @test */
    public function userId_is_required_if_you_are_principal()
    {
        $this->be($this->principal);

        Livewire::test('messages.create')
            ->call('createNewRoom')
            ->assertHasErrors(['userId']);
    }

    /** @test */
    public function role_user_can_create_new_conversation_with_principals()
    {
        $this->be($this->user1);

        Livewire::test('messages.create')
            ->call('createNewRoom')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('messages.create.thread_created'),
            ]);

        $this->assertDatabaseHas('conversations', [
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user1->full_name,
            'private' => true,
            'organization_id' => $this->org->id,
            'group_id' => null,
            'participation_id' => $this->group1->id,
            'creator_id' => $this->user1->id,
            'chat_type' => 'single-group-user',
        ]);
    }

    /** @test */
    public function role_user_cannot_create_duplicate_conversation_with_principals()
    {
        $this->be($this->user1);

        Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user1->full_name,
            'chat_type' => 'single-group-user',
            'organization_id' => $this->user1->organization_id,
            'group_id' => null,
            'creator_id' => $this->user1->id,
            'private' => true,
            'participation_id' => $this->user1->userGroup()->id,
        ]);

        Livewire::test('messages.create')
            ->call('createNewRoom')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('messages.create.duplicate_group_error'),
            ]);
    }

    /** @test */
    public function role_principal_can_create_new_conversation_with_their_groups_single_user()
    {
        $this->be($this->principal);

        Livewire::test('messages.create')
            ->set('userId', $this->user1->id)
            ->call('createNewRoom')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('messages.create.thread_created'),
            ]);

        $this->assertDatabaseHas('conversations', [
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user1->full_name,
            'private' => true,
            'organization_id' => $this->org->id,
            'group_id' => null,
            'participation_id' => $this->group1->id,
            'creator_id' => $this->user1->id,
            'chat_type' => 'single-group-user',
        ]);
    }

    /** @test */
    public function role_principal_cannot_create_duplicate_conversation_with_same_groups_user()
    {
        $this->be($this->principal);

        Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user1->full_name,
            'chat_type' => 'single-group-user',
            'organization_id' => $this->user1->organization_id,
            'group_id' => null,
            'creator_id' => $this->user1->id,
            'private' => true,
            'participation_id' => $this->user1->userGroup()->id,
        ]);

        Livewire::test('messages.create')
            ->set('userId', $this->user1->id)
            ->call('createNewRoom')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('messages.create.duplicate_group_error'),
            ]);
    }

    /** @test */
    public function role_manager_can_create_new_conversation_with_single_user()
    {
        $this->be($this->manager);

        Livewire::test('messages.create')
            ->set('userId', $this->user1->id)
            ->call('createNewRoom')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('messages.create.thread_created'),
            ]);

        $this->assertDatabaseHas('conversations', [
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user1->full_name,
            'private' => true,
            'organization_id' => $this->org->id,
            'group_id' => null,
            'participation_id' => $this->group1->id,
            'creator_id' => $this->user1->id,
            'chat_type' => 'single-group-user',
        ]);
    }

    /** @test */
    public function role_manager_cannot_create_duplicate_conversation_with_same_user()
    {
        $this->be($this->manager);

        Conversation::create([
            'title' => 'messages.roles.principals',
            'alt_title' => $this->user1->full_name,
            'chat_type' => 'single-group-user',
            'organization_id' => $this->user1->organization_id,
            'group_id' => null,
            'creator_id' => $this->user1->id,
            'private' => true,
            'participation_id' => $this->user1->userGroup()->id,
        ]);

        Livewire::test('messages.create')
            ->set('userId', $this->user1->id)
            ->call('createNewRoom')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('messages.create.duplicate_group_error'),
            ]);
    }
}
