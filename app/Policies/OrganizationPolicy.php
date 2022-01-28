<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationPolicy
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
        return false;
    }

    // public function view(User $user, Organization $organization)
    // {
    //     return false;
    // }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Organization $organization)
    {
        return false;
    }

    // public function delete(User $user, Organization $organization)
    // {
    //     return false;
    // }
}
