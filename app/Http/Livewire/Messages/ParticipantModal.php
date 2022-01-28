<?php

namespace App\Http\Livewire\Messages;

use App\Http\Livewire\Component;
use App\Models\Conversation;
use App\Models\ConversationNotification;
use App\Models\User;
use App\Traits\HasConversationParticipants;

class ParticipantModal extends Component
{
    use HasConversationParticipants;

    public $conversationId;
    public $modalVisible = false;
    public $participants = [];
    public $participantIds = [];

    protected $listeners = ['updateValueByKey', 'userChangedTheThread'];

    public function mount()
    {
        $this->userChangedTheThread(auth()->user()->active_thread);
    }

    public function userChangedTheThread($id)
    {
        $this->conversationId = $id;
    }

    private function checkForCustomParticipants()
    {
        if (! auth()->user()->isManager()) {
            return;
        }

        $participantIds = [];
        if ($this->conversationId) {
            $participantIds = collect($this->participants)->pluck('id')->all();
        }

        $this->emit('messages.thread.selected-participants.updated', [
            'key' => 'selected',
            'value' => $participantIds,
        ]);

        $this->participantIds = $participantIds;

        $this->emit('messages.thread.extra-limitor.updated', [
            'key' => 'extraLimitor',
            'value' => [
                'role' => ['User', 'Principal'],
                'organization_id' => auth()->user()->organization_id,
            ],
        ]);
    }

    public function getCanShowUpdateFormProperty()
    {
        if ($this->conversationId && auth()->user()->isManager()) {
            $conversation = Conversation::findOrFail($this->conversationId);

            return $conversation->chat_type == 'custom';
        }

        return false;
    }

    public function showParticipantsModal()
    {
        if (! auth()->user()->isManager()) {
            return;
        }

        $this->updateConversationParticipants();

        $this->modalVisible = true;

        $this->checkForCustomParticipants();
    }

    private function updateConversationParticipants($returnOnly = false)
    {
        if (! $this->conversationId) {
            return;
        }

        $conversationId = $this->conversationId;

        $conversation = Conversation::query()
            ->withThreads()
            ->where('id', $conversationId)
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

    public function updateParticipants()
    {
        $this->modalVisible = false;

        if (! auth()->user()->isManager()) {
            return $this->emitMessage('error', trans('extras.admin_and_manager_only'));
        }

        $participants = $this->participantIds;
        $participants[] = auth()->id();

        $participants = collect($participants)->map(function ($item) {
            return (int) $item;
        })->all();

        $usersList = User::query()
            ->where('organization_id', auth()->user()->organization_id)
            ->whereIn('role', ['Manager', 'User', 'Principal'])
            ->whereIn('id', $participants)
            ->pluck('id')
            ->toArray();

        $conversation = Conversation::findOrFail($this->conversationId);
        $conversation->update([
            'custom_participants' => $usersList,
        ]);

        $this->emitMessage('success', trans('messages.create.update_participants_success'));
    }

    public function render()
    {
        return view('livewire.messages.participant-modal');
    }
}
