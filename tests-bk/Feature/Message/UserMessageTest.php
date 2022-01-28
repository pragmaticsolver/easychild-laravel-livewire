<?php

namespace Tests\Feature\Message;

use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserMessageTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser('User');

        $this->be($this->user);
    }

    /** @test */
    public function user_role_can_visit_messages_page()
    {
        $this->get(route('messages.index'))
            ->assertSuccessful()
            ->assertSeeLivewire('messages.create')
            ->assertSeeLivewire('messages.sidebar')
            ->assertSeeLivewire('messages.thread');
    }

    /** @test */
    public function user_role_test_message_sidebar_for_chat_threads()
    {
        $threads = Conversation::query()
            ->withThreads()
            ->withLastMessage()
            ->latest()
            ->get();

        $component = Livewire::test('messages.sidebar')
            ->assertEmitted('userChangedTheThread');

        foreach ($threads as $thread) {
            $component->call('setActiveThread', $thread->id)
                ->assertEmitted('userChangedTheThread', $thread->id);
        }
    }

    /** @test */
    public function user_role_cannot_fake_thread_that_is_not_available_to_him()
    {
        $thread = Conversation::where('chat_type', 'staffs')->first();

        $threads = Conversation::query()
            ->withThreads()
            ->withLastMessage()
            ->latest()
            ->get();

        Livewire::test('messages.sidebar')
            ->call('setActiveThread', $thread->id)
            ->assertSet('activeThread', $threads->first()->id)
            ->assertEmitted('userChangedTheThread', $threads->first()->id);
    }
}
