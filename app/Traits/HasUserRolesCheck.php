<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait HasUserRolesCheck
{
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isContractor()
    {
        return $this->role === 'Contractor';
    }

    public function isPrincipal()
    {
        return $this->role === 'Principal';
    }

    public function isParent()
    {
        return $this->role === 'Parent';
    }

    public function isManager()
    {
        return $this->role === 'Manager';
    }

    public function isVendor()
    {
        return $this->role === 'Vendor';
    }

    public function isUser()
    {
        return $this->role === 'User';
    }

    public function isAdminOrPrincipal()
    {
        return $this->isAdmin() || $this->isPrincipal();
    }

    public function isAdminOrManager()
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function isManagerOrPrincipal()
    {
        return $this->isPrincipal() || $this->isManager();
    }

    public function hasAccessToService($service)
    {
        if ($this->isAdmin()) {
            return true;
        }

        $canAccess = false;

        $organization = $this->organization;

        if ($this->isParent()) {
            $organization = $this->parent_current_child->organization;
        }

        if ($this->isContractor()) {
            return false;
        }

        $orgSettings = $organization->settings;

        $orgAccessSettings = [];
        if (Arr::has($orgSettings, 'access')) {
            $orgAccessSettings = $orgSettings['access'];
        }

        if (Arr::has($orgAccessSettings, $service)) {
            $canAccess = $orgAccessSettings[$service];
        }

        return $canAccess;
    }
}
