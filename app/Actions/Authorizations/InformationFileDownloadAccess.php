<?php

namespace App\Actions\Authorizations;

use App\Models\Information;
use App\Models\Organization;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsObject;

class InformationFileDownloadAccess
{
    use AsObject;

    public function handle($uuid)
    {
        $model = $uuid;
        if (is_string($uuid)) {
            $model = Information::findByUUIDOrFail($uuid);
        }

        $user = auth()->user();

        if ($this->rulesCheck($model, $user)) {
            return $model;
        }

        return false;
    }

    private function rulesCheck(Information $model, User $user)
    {
        if ($user->isManager()) {
            if ($user->organization_id == $model->organization_id) {
                return true;
            }
        }

        if ($user->isVendor() || $user->isPrincipal()) {
            if ($user->organization_id == $model->organization_id) {
                return $this->roleCheck($model, $user->role);
            }
        }

        if ($user->isParent()) {
            if (! $this->roleCheck($model, 'User')) {
                return false;
            }

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

            if (in_array($model->organization_id, $orgIds)) {
                return true;
            }
        }

        if ($user->isUser()) {
            if ($user->organization_id == $model->organization_id) {
                if (! $this->roleCheck($model, 'User')) {
                    return false;
                }
            }
        }

        return false;
    }

    private function roleCheck(Information $model, $role)
    {
        if (in_array($role, $model->roles)) {
            return true;
        }

        return false;
    }
}
