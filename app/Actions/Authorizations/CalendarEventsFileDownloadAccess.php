<?php

namespace App\Actions\Authorizations;

use App\Models\CalendarEvent;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsObject;

class CalendarEventsFileDownloadAccess
{
    use AsObject;

    public function handle($uuid)
    {
        $model = $uuid;
        if (is_string($uuid)) {
            $model = CalendarEvent::findByUUIDOrFail($uuid);
        }

        $user = auth()->user();
        if ($this->rulesCheck($model, $user)) {
            return $model;
        }

        return false;
    }

    private function rulesCheck(CalendarEvent $model, User $user)
    {
        if ($user->isManager()) {
            if ($user->organization_id == $model->organization_id) {
                return true;
            }
        }

        if ($user->isPrincipal()) {
            if ($user->organization_id == $model->organization_id) {
                if ($model->groups && count($model->groups)) {
                    $userGroups = $user->groups->pluck('id')->all();

                    foreach ($userGroups as $g) {
                        if (in_array($g, $model->groups)) {
                            return true;
                        }
                    }

                    return false;
                }

                return true;
            }
        }

        if ($user->isParent()) {
            $orgIds = Organization::query()
                ->whereIn('organizations.id', function ($q) {
                    $q->select('users.organization_id')
                        ->from('users')
                        ->where('users.role', 'User')
                        ->whereIn('users.id', function ($q) {
                            $q->select('parent_child.child_id')
                                ->from('parent_child')
                                ->where('parent_child.parent_id', auth()->id());
                        });
                })->pluck('organizations.id')->all();

            if (! in_array($model->organization_id, $orgIds)) {
                return false;
            }

            if (! ($model && count($model->groups))) {
                return true;
            }

            $groupIds = Group::query()
                ->whereIn('groups.id', function ($q) {
                    $q->select('group_user.group_id')
                        ->from('group_user')
                        ->whereIn('group_user.user_id', function ($q) {
                            $q->select('parent_child.child_id')
                                ->from('parent_child')
                                ->where('parent_child.parent_id', auth()->id());
                        });
                })->pluck('groups.id')->all();

            foreach ($groupIds as $g) {
                if (in_array($g, $model->groups)) {
                    return true;
                }
            }
        }
    }
}
