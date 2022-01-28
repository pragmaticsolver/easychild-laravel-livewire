<?php

namespace App\Policies;

use App\Models\Information;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InformationPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Information $model)
    {
        if ($user->isManager() && $user->organization_id === $model->organization_id) {
            return true;
        }

        return false;
    }
}
