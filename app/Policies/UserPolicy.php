<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        if ($user->isManager()) {
            return true;
        }

        if ($user->isPrincipal()) {
            return true;
        }

        return false;
    }

    // public function view(User $user, User $model)
    // {
    //     if ($user->isPrincipal() && $user->organization_id === $model->organization_id) {
    //         return true;
    //     }

    //     if ($user->is($model)) {
    //         return true;
    //     }

    //     return false;
    // }

    public function create(User $user)
    {
        if ($user->isManager()) {
            return true;
        }

        return false;
    }

    public function update(User $user, User $model)
    {
        if ($user->isManager() && $user->organization_id === $model->organization_id) {
            return true;
        }

        if ($user->isPrincipal()) {
            $principalGroupUsers = User::query()
                ->where('role', 'User')
                ->whereIn('id', function ($query) use ($user) {
                    $query->select('user_id')
                        ->from('group_user')
                        ->whereIn('group_id', function ($query) use ($user) {
                            $query->select('group_id')
                                ->from('group_user')
                                ->where('user_id', $user->id);
                        });
                })
                ->get();

            return $principalGroupUsers->contains($model);
        }

        return false;
    }

    public function updateAvatarAndEatsOnsite(User $user, User $model)
    {
        if ($user->isManager() && $user->organization_id === $model->organization_id) {
            return true;
        }

        if ($user->isPrincipal()) {
            $principalGroupUsers = User::query()
                ->where('role', 'User')
                ->whereIn('id', function ($query) use ($user) {
                    $query->select('user_id')
                        ->from('group_user')
                        ->whereIn('group_id', function ($query) use ($user) {
                            $query->select('group_id')
                                ->from('group_user')
                                ->where('user_id', $user->id);
                        });
                })
                ->get();

            return $principalGroupUsers->contains($model);
        }

        if ($user->isParent()) {
            return $user->childrens->contains($model);
        }

        return false;
    }

    public function delete(User $user, User $model)
    {
        if ($user->isUser()) {
            return false;
        }

        // cannot delete yourself
        if ($user->is($model)) {
            return false;
        }

        if ($user->isManager() && $user->organization_id === $model->organization_id) {
            return true;
        }

        return false;
    }
}
