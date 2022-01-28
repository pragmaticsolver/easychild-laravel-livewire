<?php

namespace App\Traits;

use App\Models\User;

trait HasConversationPolicies
{
    public function canTalkToConversation($conversation)
    {
        if (! $conversation) {
            return false;
        }

        if ($this->isParent()) {
            return $this->canParentTalkToConversation($conversation);
        }

        if ($this->isPrincipal()) {
            return $this->canPrincipalTalkToConversation($conversation);
        }

        if ($this->isManager()) {
            return $this->canManagerTalkToConversation($conversation);
        }

        return false;
    }

    private function canParentTalkToConversation($conversation)
    {
        if (! $conversation->private) {
            return false;
        }

        $currentChild = $this->parent_current_child;

        if ($conversation->organization_id != $currentChild->organization_id) {
            return false;
        }

        $chatGroups = ['single-group-user', 'custom'];
        if (! in_array($conversation->chat_type, $chatGroups)) {
            return false;
        }

        if ($conversation->chat_type == 'custom') {
            if ($conversation->custom_participants && in_array($currentChild->id, $conversation->custom_participants)) {
                return true;
            }

            return false;
        }

        if ($conversation->creator_id != $currentChild->id) {
            return false;
        }

        $groupId = $currentChild->userGroup()->id;
        if ($conversation->participation_id != $groupId) {
            return false;
        }

        return true;
    }

    private function canUserTalkToConversation($conversation)
    {
        if (! $conversation->private) {
            return false;
        }

        if ($conversation->organization_id != $this->organization_id) {
            return false;
        }

        $chatGroups = ['single-group-user', 'custom'];
        if (! in_array($conversation->chat_type, $chatGroups)) {
            return false;
        }

        if ($conversation->chat_type == 'custom') {
            if ($conversation->custom_participants && in_array($this->id, $conversation->custom_participants)) {
                return true;
            }

            return false;
        }

        if ($conversation->creator_id != $this->id) {
            return false;
        }

        $groupId = $this->userGroup()->id;
        if ($conversation->participation_id != $groupId) {
            return false;
        }

        return true;
    }

    private function canPrincipalTalkToConversation($conversation)
    {
        if ($conversation->organization_id != $this->organization_id) {
            return false;
        }

        $chatTypes = ['users', 'staffs', 'principals', 'single-user', 'single-group-user', 'custom'];
        if (! in_array($conversation->chat_type, $chatTypes)) {
            return false;
        }

        if ($conversation->chat_type == 'single-user') {
            $participants = [$conversation->creator_id, $conversation->participation_id];

            if (in_array($this->id, $participants)) {
                return true;
            }

            return false;
        }

        if ($conversation->chat_type == 'custom') {
            if ($conversation->custom_participants && in_array($this->id, $conversation->custom_participants)) {
                return true;
            }

            return false;
        }

        if ($conversation->chat_type == 'users') {
            if ($conversation->group_id) {
                $currentGroup = $this->principal_current_group;

                if ($conversation->group_id == $currentGroup->id) {
                    return true;
                }
            } else {
                return false;
            }
        }

        $groups = $this->groups->pluck('id')->toArray();
        if ($conversation->chat_type == 'single-group-user') {
            if (in_array($conversation->participation_id, $groups)) {
                return true;
            }

            return false;
        }

        if (! $conversation->private) {
            if (is_null($conversation->group_id)) {
                return true;
            }

            if (in_array($conversation->group_id, $groups)) {
                return true;
            }
        }

        return false;
    }

    private function canManagerTalkToConversation($conversation)
    {
        if ($conversation->organization_id != $this->organization_id) {
            return false;
        }

        // $chatTypes = ['users', 'staffs', 'principals', 'single-user', 'single-group-user'];
        // if (! in_array($conversation->chat_type, $chatTypes)) {
        //     return false;
        // }

        if ($conversation->chat_type == 'single-user') {
            $participants = [$conversation->creator_id, $conversation->participation_id];

            if (in_array($this->id, $participants)) {
                return true;
            }

            return false;
        }

        if ($conversation->chat_type == 'custom') {
            if ($conversation->custom_participants && in_array($this->id, $conversation->custom_participants)) {
                return true;
            }

            if ($conversation->creator_id && $this->id == $conversation->creator_id) {
                return true;
            }

            return false;
        }

        $groups = $this->organization->groups->pluck('id')->toArray();
        if ($conversation->chat_type == 'single-group-user') {
            if (in_array($conversation->participation_id, $groups)) {
                return true;
            }

            return false;
        }

        if (! $conversation->private) {
            if (is_null($conversation->group_id)) {
                return true;
            }

            if (in_array($conversation->group_id, $groups)) {
                return true;
            }
        }

        return false;
    }
}
