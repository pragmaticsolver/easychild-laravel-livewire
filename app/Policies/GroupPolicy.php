<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy
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

        return false;
    }

    public function create(User $user)
    {
        if ($user->isManager()) {
            return true;
        }

        return false;
    }

    public function update(User $user, Group $group)
    {
        if ($user->isManager() && $user->organization_id == $group->organization_id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Group $group)
    {
        if ($user->isManager() && $user->organization_id == $group->organization_id) {
            return true;
        }

        return false;
    }
}
