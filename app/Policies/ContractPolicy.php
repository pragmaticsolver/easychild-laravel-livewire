<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function update(User $user, Contract $model)
    {
        if ($user->isManager()) {
            if ($model->organization_id == $user->organization_id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, Contract $model)
    {
        if ($user->isManager()) {
            if ($model->organization_id == $user->organization_id) {
                return true;
            }
        }

        return false;
    }
}
