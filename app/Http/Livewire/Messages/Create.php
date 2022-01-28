<?php

namespace App\Http\Livewire\Messages;

use App\Http\Livewire\Component;
use App\Models\Conversation;
use App\Models\User;
use App\Traits\HasConversationPartners;
use Illuminate\Validation\Rule;

class Create extends Component
{
    use HasConversationPartners;

    public $enabled = false;

    public $userId;
    public $chatTitle;
    public $userIds = [];
    public $needsUserSelector = false;
    public $creatingMultiple = false;

    protected $listeners = ['updateValueByKey'];

    public function mount()
    {
        $this->updateUsersList();
    }

    private function updateUsersList()
    {
        $user = auth()->user();

        if ($user->isPrincipal() || $user->isManager()) {
            $this->needsUserSelector = true;
        }
    }

    public function updatedCreatingMultiple()
    {
        if (! auth()->user()->isManager()) {
            $this->creatingMultiple = false;
        }
    }

    public function getUsersListProviderProperty()
    {
        $user = auth()->user();

        $usersListProvider = [
            'model' => 'user',
            'key' => 'id',
            'text' => 'full_name',
            'secondaryText' => ['role'],
            'limitKey' => 'organization_id',
            'limitValue' => $user->organization_id,
            'onlySelect' => ['id', 'uuid', 'given_names', 'last_name', 'role'],
            'orderBy' => 'given_names',
        ];

        $usersListProvider['extraWhereLimitation'] = [
            ['id', '!=', $user->id],
            ['role', '!=', 'Parent'],
            ['role', '!=', 'Vendor'],
        ];

        if ($user->isPrincipal()) {
            $usersListProvider['extraWhereLimitation'] = [
                ['id', '!=', $user->id],
                ['role', '=', 'User'],
            ];

            $usersListProvider['extraWhereInLimitation'] = [
                'id' => $this->getPrincipalUsersListInGroup($user),
            ];
        }

        return $usersListProvider;
    }

    public function updatedGroupId()
    {
        $this->updateUsersList();
    }

    public function showCreateNewRoomModal()
    {
        $this->userId = null;
        $this->userIds = [];
        $this->chatTitle = null;
        $this->emit('messages.create.userId.updated', null);

        $this->emit('messages.create.selected-participants.updated', [
            'key' => 'selected',
            'value' => $this->userIds,
        ]);

        $this->enabled = true;
        $this->creatingMultiple = false;
    }

    public function createNewRoom()
    {
        $user = auth()->user();

        $this->validate([
            'userId' => [
                'nullable',
                Rule::requiredIf(function () use ($user) {
                    return ! $user->isUser() && ! $user->isManager();
                }),
            ],
            'userIds' => [
                'array',
                Rule::requiredIf(function () use ($user) {
                    return $this->creatingMultiple;
                }),
            ],
            'chatTitle' => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->creatingMultiple;
                }),
            ],
        ]);

        $participant = $this->userId;

        if ($user->isManager() && $this->creatingMultiple) {
            $participant = $this->userIds;
            $canCreateRoom = true;
        } else {
            $canCreateRoom = $user->checkIfUserCanCreateRoomWith($participant);
        }

        if (! $canCreateRoom) {
            $this->emitMessage('error', trans('messages.create.duplicate_group_error'));
            $this->enabled = false;

            return;
        }

        $data = [
            'title' => $user->full_name,
            'alt_title' => $user->full_name,
            'chat_type' => 'single-user',
            'organization_id' => $user->organization_id,
            'group_id' => null,
            'creator_id' => null,
            'private' => true,
            'participation_id' => null,
        ];

        if ($user->isUser()) {
            $data['title'] = 'messages.roles.principals';
            $data['creator_id'] = $user->id;
            $data['participation_id'] = $user->userGroup()->id;
            $data['chat_type'] = 'single-group-user';
        }

        if ($user->isManager()) {
            if ($this->creatingMultiple) {
                $orgParticipant = User::query()
                    ->where('organization_id', $user->organization_id)
                    ->whereIn('id', collect($participant)->add($user->id)->all())
                    ->pluck('id')
                    ->toArray();

                $data['alt_title'] = null;
                $data['chat_type'] = 'custom';
                $data['title'] = $this->chatTitle;
                $data['creator_id'] = $user->id;
                $data['custom_participants'] = $orgParticipant;
            } else {
                $participantUser = User::findOrFail($participant);

                if ($participantUser->isUser()) {
                    $data['title'] = 'messages.roles.principals';
                    $data['alt_title'] = $participantUser->full_name;
                    $data['creator_id'] = $participantUser->id;
                    $data['participation_id'] = $participantUser->userGroup()->id;
                    $data['chat_type'] = 'single-group-user';
                }

                if ($participantUser->isPrincipal() || $participantUser->isManager()) {
                    $data['title'] = $participantUser->full_name;
                    $data['creator_id'] = $user->id;
                    $data['participation_id'] = $participantUser->id;
                }
            }
        }

        if ($user->isPrincipal()) {
            $participantUser = User::findOrFail($participant);

            if ($participantUser->isUser()) {
                $data['title'] = 'messages.roles.principals';
                $data['alt_title'] = $participantUser->full_name;
                $data['creator_id'] = $participantUser->id;
                $data['participation_id'] = $participantUser->userGroup()->id;
                $data['chat_type'] = 'single-group-user';
            } else {
                return;
            }
        }

        Conversation::create($data);

        $this->enabled = false;
        $this->emitMessage('success', trans('messages.create.thread_created'));
        $this->emit('refreshChatSidebarThreads');
    }

    public function render()
    {
        return view('livewire.messages.create');
    }
}
