<?php

namespace App\Http\Livewire\Messages;

use App\Http\Livewire\Component;
use App\Models\Conversation;
use App\Models\ConversationNotification;
use App\Models\Message;
use App\Traits\HasConversationParticipants;
use Carbon\Carbon;

class Thread extends Component
{
    use HasConversationParticipants;

    public $conversationId;
    public $hasNext;
    public $lastMessageId;
    public $lastMessageDateTime;
    public $messages;

    protected $listeners = [
        'userChangedTheThread',
        'addNewMessageToThread',
    ];

    public function mount()
    {
        $this->hasNext = false;
        $this->lastMessageId = null;

        // if (auth()->user()->isParent()) {
        //     $currentChild = auth()->user()->parent_current_child;
        //     $this->userChangedTheThread($currentChild->active_thread);
        // }

        $this->userChangedTheThread(auth()->user()->active_thread);
    }

    public function showParticipantModal()
    {
        $this->dispatchBrowserEvent('show-participants-modal');
    }

    public function loadMoreMessages($firstLoad = false)
    {
        $messages = $this->messages;
        if ($firstLoad) {
            $messages = [];
        }
        $messagesData = [];

        $participantsIds = $this->updateConversationParticipants($this->conversationId, true);

        if ($this->hasNext || $firstLoad) {
            $query = Message::query()
                ->whereNotIn('messages.id', collect($messages)->pluck('id')->all());

            if ($this->hasNext && $this->lastMessageDateTime) {
                $query->where('messages.created_at', '<=', $this->lastMessageDateTime);
            }

            $messagesData = $query->where('messages.conversation_id', $this->conversationId)
                ->messageWithUser()
                ->addReadAndSeenBy($participantsIds->pluck('id'))
                ->orderBy('messages.created_at', 'desc')
                ->limit(16)
                ->get()
                ->toArray();

            if (count($messagesData) > 15) {
                $lastMessage = array_pop($messagesData);
                $this->hasNext = true;
                $this->lastMessageId = $lastMessage['id'];
                $this->lastMessageDateTime = $lastMessage['created_at'];
            } else {
                $this->hasNext = false;
            }
        }

        foreach ($messagesData as $message) {
            // $createdAt = Carbon::parse($message['created_at'])->diffForHumans();
            $createdAt = Carbon::parse($message['created_at'])->format(config('setting.format.datetime'));
            $message['created_at_formatted'] = $createdAt;

            if ($message['deleted_at']) {
                $deletedAt = Carbon::parse($message['deleted_at'])->format(config('setting.format.datetime'));
                $message['deleted_at_formatted'] = $deletedAt;
            }

            array_unshift($this->messages, $message);
        }

        $this->dispatchBrowserEvent("conversation-{$this->conversationId}-participant-updated", count($participantsIds));
    }

    private function updateConversationParticipants($conversationId, $returnOnly = false)
    {
        if (! $conversationId) {
            return;
        }

        $user = auth()->user();

        $conversation = Conversation::query()
            ->withThreads()
            ->where('conversations.id', $conversationId)
            ->first();

        $participants = $this->getConversationParticipants($conversation)
            ->addSelect([
                'last_seen_at' => ConversationNotification::select('read_at')
                    ->where('conversation_id', $conversation->id)
                    ->whereColumn('user_id', 'users.id')
                    ->latest('read_at')
                    ->limit(1),
            ])->withCasts([
                'last_seen_at' => 'datetime',
            ])->get();

        if ($returnOnly) {
            return $participants;
        }

        $this->participants = $participants;
    }

    public function addNewMessageToThread($messageId, $conversationId)
    {
        if ($this->conversationId == $conversationId) {
            $message = Message::query()
                ->where('messages.id', $messageId)
                ->messageWithUser()
                ->addReadAndSeenBy()
                ->first()
                ->toArray();
            $createdAt = Carbon::parse($message['created_at'])->diffForHumans();
            $message['created_at_formatted'] = $createdAt;

            array_push($this->messages, $message);

            $this->dispatchBrowserEvent('message-scroll-to-latest');

            auth()->user()->updateConversationNotification($conversationId);
        }
    }

    public function userChangedTheThread($id)
    {
        $this->conversationId = $id;

        if ($this->conversationId) {
            $this->hasNext = false;
            $this->lastMessageId = null;
            $this->messages = [];

            auth()->user()->updateConversationNotification($id);

            $this->dispatchBrowserEvent('message-scroll-to-latest');

            $this->loadMoreMessages(true);
        }
    }

    public function render()
    {
        return view('livewire.messages.thread');
    }
}
