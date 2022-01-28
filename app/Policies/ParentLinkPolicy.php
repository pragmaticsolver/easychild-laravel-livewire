<?php

namespace App\Policies;

use App\Models\ParentLink;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParentLinkPolicy
{
    use HandlesAuthorization;

    public function update(User $user, ParentLink $parentLink)
    {
        $child = User::query()
            ->where('id', $parentLink->child_id)
            ->first();

        if ($child && $child->organization_id == $user->organization_id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, ParentLink $parentLink)
    {
        if ($user->isPrincipal()) {
            return false;
        }

        $child = User::query()
            ->where('id', $parentLink->child_id)
            ->first();

        if ($child && $child->organization_id == $user->organization_id) {
            return true;
        }

        return false;
    }
}
