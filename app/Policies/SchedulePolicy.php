<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\Organization;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user, $model)
    {
        if ($user->isManager() && $model) {
            if (
                $model instanceof Organization
                && $user->organization_id === $model->id
            ) {
                return true;
            }

            if ($model instanceof Group) {
                if ($user->groups->contains($model) || $user->organization_id === $model->organization_id) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Schedule $model)
    {
        if ($user->isManager()) {
            if ($model->user->organization_id === $user->organization_id) {
                return true;
            }
        }

        if ($user->isPrincipal()) {
            $usersCount = User::query()
                ->where('users.role', 'User')
                ->where('users.organization_id', $user->organization_id)
                ->where('users.id', $model->user_id)
                ->whereIn('users.id', function ($query) use ($user) {
                    $query->select('user_id')
                        ->from('group_user')
                        ->whereIn('group_user.group_id', function ($query) use ($user) {
                            $query->select('group_id')
                                ->from('group_user')
                                ->where('group_user.user_id', $user->id);
                        });
                })->count();

            return ! ! $usersCount;
        }

        if ($user->isParent()) {
            return $user->childrens->contains($model->user_id);
        }

        return false;
    }
}
