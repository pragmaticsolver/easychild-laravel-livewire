<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function update(User $user, Contact $model)
    {
        $modelUser = User::findOrFail($model->user_id);

        if ($user->isManagerOrPrincipal()) {
            if ($modelUser->organization_id == $user->organization_id) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, Contact $model)
    {
        $modelUser = User::findOrFail($model->user_id);

        if ($user->isManager()) {
            if ($modelUser->organization_id == $user->organization_id) {
                return true;
            }
        }

        return false;
    }
}
