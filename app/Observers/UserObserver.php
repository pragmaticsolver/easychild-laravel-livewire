<?php

namespace App\Observers;

use App\Actions\User\RemoveUserRelatedDataWhenDeletedAction;
use App\Models\User;

class UserObserver
{
    public function deleting(User $user)
    {
        RemoveUserRelatedDataWhenDeletedAction::run($user);
    }

    public function creating($obj)
    {
        if ($obj->role == 'Parent') {
            $settings = $obj->settings;

            if (! $settings) {
                $settings = [];
            }

            $settings['mail'] = true;

            $obj->settings = $settings;
        }
    }
}
