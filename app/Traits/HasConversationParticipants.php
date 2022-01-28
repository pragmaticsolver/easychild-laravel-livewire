<?php

namespace App\Traits;

use App\Models\Conversation;
use App\Models\User;

trait HasConversationParticipants
{
    public function getConversationParticipants(Conversation $conversation)
    {
        if ($conversation->chat_type == 'users') {
            return $this->getUserChatTypeParticipants($conversation);
        }

        if ($conversation->chat_type == 'single-group-user') {
            return $this->getSingleGroupUserChatTypeParticipants($conversation);
        }

        if ($conversation->chat_type == 'managers') {
            return $this->getManagersChatTypeParticipants($conversation);
        }

        if ($conversation->chat_type == 'staffs') {
            return $this->getStaffsChatTypeParticipants($conversation);
        }

        if ($conversation->chat_type == 'principals') {
            return $this->getPrincipalsChatTypeParticipants($conversation);
        }

        if ($conversation->chat_type == 'single-user') {
            return $this->getSingleUserChatTypeParticipants($conversation);
        }

        if ($conversation->chat_type == 'custom') {
            return $this->getCustomChatTypeParticipants($conversation);
        }
    }

    private function getCustomChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;

        $userIdList = $conversation->custom_participants;
        if (! $userIdList) {
            $userIdList = [];
        }

        $userIdList[] = $conversation->creator_id;

        return User::query()
            ->where('organization_id', $orgId)
            ->whereIn('id', $userIdList);
    }

    private function getSingleUserChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;

        return User::query()
            ->where('organization_id', $orgId)
            ->whereIn('id', [$conversation->creator_id, $conversation->participation_id]);
    }

    private function getSingleGroupUserChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;
        $groupId = $conversation->participation_id;

        return User::query()
            ->where(function ($q) use ($orgId, $groupId, $conversation) {
                $q->where('users.organization_id', $orgId)
                    ->whereIn('users.role', ['Manager', 'Principal'])
                    ->where(function ($q) use ($groupId, $conversation) {
                        $q->where('users.id', $conversation->creator_id)
                            ->orWhere(function ($q) use ($groupId) {
                                $q->whereHas('groups', function ($q) use ($groupId) {
                                    $q->where('groups.id', $groupId);
                                });
                            });
                    });
            })
            ->orWhere(function ($q) use ($orgId, $groupId, $conversation) {
                $q->where('users.role', 'Parent');
                $q->whereIn('users.id', function ($q) use ($orgId, $groupId, $conversation) {
                    $q->select('parent_child.parent_id')
                        ->from('parent_child')
                        ->where('parent_child.child_id', $conversation->creator_id);
                    // ->whereIn('parent_child.child_id', function ($q) use ($orgId, $groupId) {
                        //     $q->select('group_user.user_id')
                        //         ->from('group_user')
                        //         ->where('group_user.group_id', $groupId)
                        //         ->whereIn('group_user.group_id', function ($q) use ($orgId) {
                        //             $q->select('groups.id')
                        //                 ->from('groups')
                        //                 ->where('groups.organization_id', $orgId);
                        //         });
                        // });
                });
            });
    }

    private function getPrincipalsChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;
        $groupId = $conversation->group_id;

        return User::query()
            ->where('organization_id', $orgId)
            ->where(function ($q) use ($groupId) {
                $q->where('role', 'Manager')
                    ->orWhere(function ($q) use ($groupId) {
                        $q->where('role', 'Principal')
                            ->whereHas('groups', function ($q) use ($groupId) {
                                $q->where('groups.id', $groupId);
                            });
                    });
            });
    }

    private function getStaffsChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;

        return User::query()
            ->where('organization_id', $orgId)
            ->whereIn('role', ['Principal', 'Manager']);
    }

    private function getManagersChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;

        return User::query()
            ->where('organization_id', $orgId)
            ->whereIn('role', ['Manager']);
    }

    private function getUserChatTypeParticipants(Conversation $conversation)
    {
        $orgId = $conversation->organization_id;
        $groupId = $conversation->group_id;

        return User::query()
            ->where(function ($q) use ($orgId, $groupId) {
                $q->where('users.organization_id', $orgId);

                $q->where(function ($q) use ($orgId, $groupId) {
                    $q->where('users.role', 'Manager');

                    $q->orWhere(function ($q) use ($orgId, $groupId) {
                        $q->where('users.role', 'Principal')
                            ->when($groupId, function ($q) use ($groupId) {
                                $q->whereHas('groups', function ($q) use ($groupId) {
                                    $q->where('groups.id', $groupId);
                                });
                            });
                    });
                });
            })
            ->orWhere(function ($q) use ($orgId, $groupId) {
                $q->where('users.role', 'Parent');
                $q->whereIn('users.id', function ($q) use ($orgId, $groupId) {
                    $q->when($groupId, function ($q) use ($groupId, $orgId) {
                        $q->select('parent_child.parent_id')
                            ->from('parent_child')
                            ->whereIn('parent_child.child_id', function ($q) use ($groupId, $orgId) {
                                $q->select('group_user.user_id')
                                    ->from('group_user')
                                    ->where('group_user.group_id', $groupId)
                                    ->whereIn('group_user.group_id', function ($q) use ($orgId) {
                                        $q->select('groups.id')
                                            ->from('groups')
                                            ->where('groups.organization_id', $orgId);
                                    });
                            });
                    }, function ($q) use ($orgId) {
                        $q->select('parent_child.parent_id')
                            ->from('parent_child')
                            ->whereIn('parent_child.child_id', function ($q) use ($orgId) {
                                $q->select('group_user.user_id')
                                    ->from('group_user')
                                    ->whereIn('group_user.group_id', function ($q) use ($orgId) {
                                        $q->select('groups.id')
                                            ->from('groups')
                                            ->where('groups.organization_id', $orgId);
                                    });
                            });
                    });
                });
            });
    }
}
