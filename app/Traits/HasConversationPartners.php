<?php

namespace App\Traits;

use App\Models\User;

trait HasConversationPartners
{
    private function getPrincipalUsersListInGroup($principal)
    {
        $groups = $principal->groups->pluck('id')->toArray();

        return User::query()
            ->join('group_user', 'users.id', 'group_user.user_id')
            ->whereIn('group_user.group_id', $groups)
            ->pluck('users.id')
            ->toArray();
    }

    private function getPrincipalPrivateChatUsers($principal)
    {
        return User::query()
            ->where('organization_id', $principal->organization_id)
            ->where('id', '!=', $principal->id)
            ->whereIn('id', function ($query) use ($principal) {
                $query->select('user_id')
                    ->from('group_user')
                    ->whereIn('group_user.group_id', function ($query) use ($principal) {
                        $query->select('group_id')
                            ->from('group_user')
                            ->where('group_user.user_id', $principal->id);
                    });
            })
            ->select('id', 'uuid', 'given_names', 'last_name', 'role')
            ->get();
    }

    private function getParentPrivateChatUsers($user)
    {
        $currentChild = $user->parent_current_child;

        return User::query()
            ->where('organization_id', $currentChild->organization_id)
            ->where('role', 'Principal')
            ->whereIn('id', function ($query) use ($currentChild) {
                $query->select('user_id')
                    ->from('group_user')
                    ->whereIn('group_user.group_id', function ($query) use ($currentChild) {
                        $query->select('group_id')
                            ->from('group_user')
                            ->where('group_user.user_id', $currentChild->id);
                    });
            })
            ->select('id', 'uuid', 'given_names', 'last_name', 'role')
            ->get();
    }

    private function getUserPrivateChatUsers($user)
    {
        return User::query()
            ->where('organization_id', $user->organization_id)
            ->where('role', 'Principal')
            ->whereIn('id', function ($query) use ($user) {
                $query->select('user_id')
                    ->from('group_user')
                    ->whereIn('group_user.group_id', function ($query) use ($user) {
                        $query->select('group_id')
                            ->from('group_user')
                            ->where('group_user.user_id', $user->id);
                    });
            })
            ->select('id', 'uuid', 'given_names', 'last_name', 'role')
            ->get();
    }

    private function getManagerPrivateChatUsers($manager)
    {
        return User::query()
            ->where('organization_id', $manager->organization_id)
            ->where('id', '!=', $manager->id)
            ->whereNotIn('role', ['Parent', 'Vendor'])
            ->select('id', 'uuid', 'given_names', 'last_name', 'role')
            ->get();
    }
}
