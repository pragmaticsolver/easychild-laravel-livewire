<?php

namespace App\Http\Livewire\Messages;

use App\Models\Conversation;
use App\Models\ConversationNotification;
use Carbon\Carbon;
use Livewire\Component;
use App\Traits\HasConversationParticipants;

class ReadModal extends Component
{
    use HasConversationParticipants;

    public $showModal = false;
    public $participants = [];
    public $message = null;


    protected $listeners = [
        'message-status-show-modal' => 'showReadModal',
    ];


    public function showReadModal($params)
    {
        $message = $params['message'];
        $this->message = $message;

        $conversation = Conversation::query()
            ->withThreads()
            ->where('id', $message['conversation_id'])
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

        $message_created_at = Carbon::parse($message['created_at']);

        $participants->map(function ($item) use ($message_created_at){
            if($item->last_seen_at == null) {
                $item->read = false;
            } else {
                $last_seen_at = Carbon::parse($item->last_seen_at);
                $result = $last_seen_at->gt($message_created_at);
                $item->read = $result;
            }
            return $item;
        });

        $this->participants = $participants;

        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.messages.read-modal');
    }
}
