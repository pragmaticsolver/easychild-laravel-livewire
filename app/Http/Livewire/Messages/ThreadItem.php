<?php

namespace App\Http\Livewire\Messages;

use App\Http\Livewire\Component;
use App\Models\Conversation;
use App\Models\ConversationNotification;
use App\Models\Message;
use App\Traits\HasConversationParticipants;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ThreadItem extends Component
{
    use HasConversationParticipants;
    public $message;
    public $participants = [];

    public function mount($message)
    {

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

        $this->message = $message;
        $this->participants = $participants;

        $this->dispatchBrowserEvent("conversation-{$message['conversation_id']}-participant-updated", count($message));
    }

    public function showImage()
    {
        $this->emitTo('components.file-modal', 'file-model-open', [
            'name' => $this->message['attachment'],
            'type' => 'image',
            'url' => url('attachment/' . $this->message['uuid'])
        ]);
    }

    public function showPDFAttachment()
    {
        $this->emitTo('components.file-modal', 'file-model-open', [
            'name' => $this->message['attachment'],
            'type' => 'pdf',
            'url' => url('attachment/' . $this->message['uuid'])
        ]);
    }

    public function download()
    {
        $path = storage_path('app/attachments/' . $this->message['conversation_id'] . '/'.$this->message['uuid'].'/'. $this->message['attachment']);
        return response()->download($path);
    }

    public function showStatus()
    {
        $this->emitTo('messages.read-modal', 'message-status-show-modal', [
            'message' => $this->message
        ]);
    }

    public function getIsSenderProperty()
    {
        return $this->message && $this->message['sender_id'] == auth()->id();
    }

    public function deleteMessage()
    {
        if ($this->isSender) {
            $message = Message::findByUUIDOrFail($this->message['uuid']);
            $message->update([
                'deleted_at' => now(),
                'body' => null,
            ]);

            $this->message = collect($this->message)->merge($message->toArray())->all();

            $deletedAt = Carbon::parse($this->message['deleted_at'])->format(config('setting.format.datetime'));
            $this->message['deleted_at_formatted'] = $deletedAt;
            if($message->type !== 'text') {
                $path = storage_path('app/attachments/' . $this->message['conversation_id'] . '/'.$this->message['uuid'].'/'. $this->message['attachment']);
                if(File::exists($path))
                {
                    Storage::deleteDirectory('/attachments/' . $this->message['conversation_id'] . '/' . $this->message['uuid']);
                }
            }
            // send push notification for deletion
        }
    }

    public function render()
    {
        return view('livewire.messages.thread-item');
    }
}
