<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CalendarEventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return mixed
     */
    public function view(User $user, CalendarEvent $calendarEvent)
    {
        if ($user->isParent()) {
            $childrens = $user->childrens()
                ->select('users.id', 'users.organization_id')
                ->get();

            if ($calendarEvent->birthday) {
                if (in_array($calendarEvent->birthday_id, $childrens->pluck('id')->toArray())) {
                    return true;
                }

                return false;
            }

            $groupIds = $childrens->map(function ($item) {
                $group = $item->groups->first();

                return $group ? $group->id : null;
            })->unique()->filter()->toArray();

            $orgIds = $childrens->pluck('organization_id')->unique()->toArray();

            $hasInOrg = in_array($calendarEvent->organization_id, $orgIds);
            $hasInChildGroup = false;

            if ($calendarEvent->groups && ! count($calendarEvent->groups)) {
                $hasInChildGroup = true;
            }

            foreach ($groupIds as $g) {
                if (in_array($g, $calendarEvent->groups)) {
                    $hasInChildGroup = true;
                }
            }

            if (in_array('User', $calendarEvent->roles) && $hasInOrg && $hasInChildGroup) {
                return true;
            }

            return false;
        }

        if ($user->organization_id != $calendarEvent->organization_id) {
            return false;
        }

        if ($user->id == $calendarEvent->creator_id) {
            return true;
        }

        if ($user->isManager()) {
            return true;
        }

        if ($user->isPrincipal()) {
            $hasInPrincipalGroup = false;

            if (! $calendarEvent->groups) {
                $hasInPrincipalGroup = true;
            }

            if ($calendarEvent->groups && ! count($calendarEvent->groups)) {
                $hasInPrincipalGroup = true;
            }

            if (! $hasInPrincipalGroup) {
                $groups = $user->groups()->pluck('groups.id')->toArray();

                foreach ($groups as $g) {
                    if (in_array($g, $calendarEvent->groups)) {
                        $hasInPrincipalGroup = true;
                    }
                }
            }

            return $hasInPrincipalGroup;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return mixed
     */
    public function update(User $user, CalendarEvent $calendarEvent)
    {
        if ($calendarEvent->birthday) {
            return false;
        }

        if ($user->organization_id != $calendarEvent->organization_id) {
            return false;
        }

        if ($user->id == $calendarEvent->creator_id) {
            return true;
        }

        if ($user->isManager()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return mixed
     */
    public function delete(User $user, CalendarEvent $calendarEvent)
    {
        if ($calendarEvent->birthday) {
            return false;
        }

        if ($user->organization_id != $calendarEvent->organization_id) {
            return false;
        }

        if ($user->id == $calendarEvent->creator_id) {
            return true;
        }

        if ($user->isManager()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return mixed
     */
    public function restore(User $user, CalendarEvent $calendarEvent)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CalendarEvent  $calendarEvent
     * @return mixed
     */
    public function forceDelete(User $user, CalendarEvent $calendarEvent)
    {
        //
    }
}
