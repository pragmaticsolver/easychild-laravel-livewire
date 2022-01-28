<?php

namespace App\Traits;

use App\Models\Conversation;

trait HasPrivateConversationCheck
{
    public function checkIfUserCanCreateRoomWith($participant = null)
    {
        if ($this->isUser()) {
            return ! $this->roleUserHasPrivateChat();
        }

        if ($this->isParent()) {
            return ! $this->roleUserHasPrivateChat();
        }

        if ($this->isManager()) {
            return ! $this->roleManagerHasPrivateChat($participant);
        }

        if ($this->isPrincipal()) {
            return ! $this->rolePrincipalHasPrivateChat($participant);
        }

        return false;
    }

    public function roleParentHasPrivateChat()
    {
        $currentChild = $this->parent_current_child;

        return ! ! Conversation::query()
            ->where('private', true)
            ->where('organization_id', $currentChild->organization_id)
            ->where('group_id', null)
            ->where('chat_type', 'single-group-user')
            ->where('creator_id', $currentChild->id)
            ->whereIn('participation_id', function ($query) use ($currentChild) {
                $query->select('group_user.group_id')
                    ->from('group_user')
                    ->where('group_user.user_id', $currentChild->id);
            })
            ->first();
    }

    public function roleUserHasPrivateChat()
    {
        return ! ! Conversation::query()
            ->where('private', true)
            ->where('organization_id', $this->organization_id)
            ->where('group_id', null)
            ->where('chat_type', 'single-group-user')
            ->where('creator_id', $this->id)
            ->whereIn('participation_id', function ($query) {
                $query->select('group_user.group_id')
                    ->from('group_user')
                    ->where('group_user.user_id', $this->id);
            })
            ->first();
    }

    public function roleManagerHasPrivateChat($participant)
    {
        return ! ! Conversation::query()
            ->where('private', true)
            ->where('organization_id', $this->organization_id)
            ->where('group_id', null)
            ->where(function ($query) use ($participant) {
                $query->orWhere(function ($query) use ($participant) {
                    $query->where([
                        'chat_type' => 'single-user',
                        'participation_id' => $participant,
                    ]);
                })->orWhere(function ($query) use ($participant) {
                    $query->where([
                        'chat_type' => 'single-group-user',
                        'creator_id' => $participant,
                    ]);
                });
            })->count();
    }

    public function rolePrincipalHasPrivateChat($participant)
    {
        return ! ! Conversation::query()
            ->where('private', true)
            ->where('organization_id', $this->organization_id)
            ->where('group_id', null)
            ->where('chat_type', 'single-group-user')
            ->where('creator_id', $participant)
            ->whereIn('participation_id', function ($query) {
                $query->select('group_user.group_id')
                    ->from('group_user')
                    ->where('group_user.user_id', $this->id);
            })
            ->count();
    }
}
