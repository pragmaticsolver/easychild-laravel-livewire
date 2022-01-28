<?php

namespace App\Traits;

use App\Models\ParentLink;

trait HasParentRelation
{
    public function childrens()
    {
        return $this->belongsToMany(static::class, 'parent_child', 'parent_id', 'child_id')
            ->where('users.role', 'User');
    }

    public function parentLinks()
    {
        return $this->hasMany(ParentLink::class, 'child_id', 'id');
    }

    public function childLinks()
    {
        return $this->hasMany(ParentLink::class, 'email', 'email');
    }

    public function parents()
    {
        return $this->belongsToMany(static::class, 'parent_child', 'child_id', 'parent_id')
            ->where('users.role', 'Parent');
    }

    public function setParentCurrentChild($childId)
    {
        $key = $this->getCacheKey();
        cache()->set($key, $childId);
    }

    public function getParentCurrentChildAttribute()
    {
        $key = $this->getCacheKey();
        $child = cache()->get($key);

        if ($child) {
            if ($foundChild = $this->childrens->where('id', $child)->first()) {
                return $foundChild;
            }
        }

        if ($firstChild = $this->childrens->first()) {
            $this->setParentCurrentChild($firstChild->id);
        }

        return $firstChild;
    }
}
