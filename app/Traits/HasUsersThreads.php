<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait HasUsersThreads
{
    public function scopeWithThreads($query, $limitPrincipalGroup = true)
    {
        $user = auth()->user();

        if ($user->isUser()) {
            return $this->getUserThreads($query, $user);
        }

        if ($user->isParent()) {
            return $this->getParentThreads($query, $user);
        }

        if ($user->isPrincipal()) {
            return $this->getPrincipalThreads($query, $user, $limitPrincipalGroup);
        }

        if ($user->isManager()) {
            return $this->getManagerThreads($query, $user);
        }
    }

    public function getParentThreads($query, $user)
    {
        $currentChild = $user->parent_current_child;

        $query->where(function ($query) use ($currentChild) {
            $query->where('conversations.organization_id', $currentChild->organization_id)
                ->where('conversations.private', false)
                ->where('conversations.group_id', null)
                ->where('conversations.chat_type', 'users');
        });

        $query->orWhere(function ($query) use ($currentChild) {
            $query->where('conversations.organization_id', $currentChild->organization_id)
                ->where('conversations.private', false)
                ->whereIn('conversations.group_id', function ($query) use ($currentChild) {
                    $query->select('group_id')
                        ->from('group_user')
                        ->where('user_id', $currentChild->id);
                })
                ->where('conversations.chat_type', 'users');
        });

        $query->orWhere(function ($query) use ($currentChild) {
            $query->where('conversations.organization_id', $currentChild->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->where('conversations.chat_type', 'custom')
                ->whereJsonContains('custom_participants', $currentChild->id);
        });

        $query->orWhere(function ($query) use ($currentChild) {
            $query->where('conversations.organization_id', $currentChild->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.participation_id', function ($query) use ($currentChild) {
                    $query->select('group_id')
                        ->from('group_user')
                        ->where('user_id', $currentChild->id);
                })
                ->where('conversations.creator_id', $currentChild->id)
                ->where('conversations.chat_type', 'single-group-user');
        });
    }

    public function getUserThreads($query, $user)
    {
        $query->where(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', false)
                ->where('conversations.group_id', null)
                ->where('conversations.chat_type', 'users');
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', false)
                ->whereIn('conversations.group_id', function ($query) use ($user) {
                    $query->select('group_id')
                        ->from('group_user')
                        ->where('user_id', $user->id);
                })
                ->where('conversations.chat_type', 'users');
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->where('conversations.chat_type', 'custom')
                ->whereJsonContains('custom_participants', $user->id);
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.participation_id', function ($query) use ($user) {
                    $query->select('group_id')
                        ->from('group_user')
                        ->where('user_id', $user->id);
                })
                ->where('conversations.creator_id', $user->id)
                ->where('conversations.chat_type', 'single-group-user');
        });
    }

    public function getPrincipalThreads($query, $user, $limitPrincipalGroup)
    {
        $groups = $user->principal_current_group_id;
        $groups = Arr::wrap($groups);

        $query->where(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', false)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.chat_type', ['users', 'staffs']);
        });

        $query->orWhere(function ($query) use ($user, $groups, $limitPrincipalGroup) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', false)
                // ->whereIn('conversations.group_id', $groups)
                ->whereIn('conversations.group_id', function ($query) use ($user, $groups, $limitPrincipalGroup) {
                    $query->select('group_id')
                        ->from('group_user')
                        ->where('user_id', $user->id);

                    if ($limitPrincipalGroup) {
                        $query->whereIn('group_id', $groups);
                    }
                })
                ->whereIn('conversations.chat_type', ['users', 'principals']);
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->where('conversations.chat_type', 'custom')
                ->whereJsonContains('custom_participants', $user->id);
        });

        $query->orWhere(function ($query) use ($user, $groups, $limitPrincipalGroup) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.participation_id', function ($query) use ($user, $groups, $limitPrincipalGroup) {
                    $query->select('group_id')
                        ->from('group_user')
                        ->where('user_id', $user->id);

                    if ($limitPrincipalGroup) {
                        $query->whereIn('group_id', $groups);
                    }
                })
                ->where('conversations.chat_type', 'single-group-user');
        });

        $query->orWhere(function ($query) use ($user, $groups) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->where('conversations.participation_id', $user->id)
                ->where('conversations.chat_type', 'single-user');
        });
    }

    public function getManagerThreads($query, $user)
    {
        $query->where(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', false)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.chat_type', ['users', 'staffs', 'managers', 'principals']);
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', false)
                ->whereIn('conversations.group_id', function ($query) use ($user) {
                    $query->select('id')
                        ->from('groups')
                        ->where('organization_id', $user->organization_id);
                })
                ->whereIn('conversations.chat_type', ['users', 'principals']);
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->where('conversations.chat_type', 'custom')
                ->whereJsonContains('custom_participants', $user->id);
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.participation_id', function ($query) use ($user) {
                    $query->select('id')
                        ->from('groups')
                        ->where('organization_id', $user->organization_id);
                })
                ->where('conversations.chat_type', 'single-group-user');
        });

        $query->orWhere(function ($query) use ($user) {
            $query->where('conversations.organization_id', $user->organization_id)
                ->where('conversations.private', true)
                ->where('conversations.group_id', null)
                ->whereIn('conversations.participation_id', function ($query) use ($user) {
                    $query->select('u1.id')
                        ->from('users as u1')
                        ->where('u1.organization_id', $user->organization_id);
                })
                ->where('conversations.chat_type', 'single-user');
        });
    }
}
