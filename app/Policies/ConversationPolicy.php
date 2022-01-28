<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Conversation $model)
    {
        if ($user->isUser()) {
            return $this->userPolicy($user, $model);
        }
    }

    private function userPolicy($user, $model)
    {
        // if ($model->group_info !== 'users');
    }
}
