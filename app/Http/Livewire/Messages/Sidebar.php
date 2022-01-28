<?php

namespace App\Http\Livewire\Messages;

use App\Http\Livewire\Component;
use App\Models\Conversation;

class Sidebar extends Component
{
    public $activeThread;
    public $search;
    private $fromEvent;

    protected $listeners = [
        'principalGroupUpdated' => '$refresh',
        'parent.child-switched' => '$refresh',
        'refreshChatSidebarThreads',
        'newMessageReceivedOnConversation',
        'refreshMessagesSidebarAndThreads' => '$refresh',
    ];

    public function mount($conversation)
    {
        if ($conversation) {
            $this->setActiveThread($conversation, false);
        } else {
            $this->setActiveThread(auth()->user()->active_thread, false);
        }
    }

    public function newMessageReceivedOnConversation($conversationId, $messageId)
    {
        if ($this->activeThread === $conversationId) {
            $this->emitTo('messages.thread', 'addNewMessageToThread', $messageId, $conversationId);
        }
    }

    public function setActiveThread($id = null, $emit = true)
    {
        if ($id) {
            $this->activeThread = $id;
            auth()->user()->setActiveThread($this->activeThread);

            if ($emit) {
                $this->emit('userChangedTheThread', $this->activeThread);
            }
        }
    }

    public function refreshChatSidebarThreads()
    {
        $this->fromEvent = true;
    }

    public function render()
    {
        $threadsQuery = Conversation::query()
            ->withThreads()
            ->withLastMessage()
            ->latest('updated_at');

        if ($this->search) {
            $threadsQuery->search($this->search);
        }

        $threads = $threadsQuery->get();

        $loadedActiveThread = $this->activeThread;
        if ($this->activeThread && ! $threads->contains($this->activeThread)) {
            $this->activeThread = $threads->first() ? $threads->first()->id : null;
            $loadedActiveThread = $this->activeThread;

            if ($this->activeThread) {
                $this->setActiveThread($this->activeThread);
            }
        }

        if (! $this->activeThread) {
            $thread = $threads->first() ? $threads->first()->id : null;
            $loadedActiveThread = $thread;

            $this->setActiveThread($thread);
        }

        $placeholder = trans('messages.search_placeholder');

        return view('livewire.messages.sidebar', compact('threads', 'placeholder', 'loadedActiveThread'));
    }
}
