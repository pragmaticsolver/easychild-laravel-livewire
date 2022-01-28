<?php

namespace App\Traits;

use App\Models\CalendarEvent;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasModelLimit
{
    public function scopeWithLimitOption($query, $config)
    {
        if (
            Arr::has($config, 'limitKey') &&
            Arr::has($config, 'limitValue')
        ) {
            $query->when(str_contains($config['limitKey'], '.'), function ($query) use ($config) {
                [$relation,] = Str::of($config['limitKey'])->explode('.');
                $value = $config['limitValue'];

                $query->whereHas($relation, function ($query) use ($config, $value) {
                    $query->where($config['limitKey'], $value);
                });
            }, function ($query) use ($config) {
                $query->where($config['limitKey'], $config['limitValue']);
            });
        }

        if (Arr::has($config, 'extraWhereLimitation')) {
            $query->where($config['extraWhereLimitation']);
        }

        if (Arr::has($config, 'extraWhereInLimitation')) {
            $whereIns = $config['extraWhereInLimitation'];

            foreach ($whereIns as $key => $whereInItem) {
                $query->whereIn($key, $whereInItem);
            }
        }

        if (Arr::has($config, 'onlySelect') && is_array($config['onlySelect'])) {
            $query->select($config['onlySelect']);
        }
    }

    public function scopeForCalendarEventAudience($query, CalendarEvent $event)
    {
        $query->when($event->roles && in_array('Manager', $event->roles), function ($query) use ($event) {
            $query->where(function ($query) use ($event) {
                $query->where('users.organization_id', $event->organization_id)
                    ->where('users.role', 'Manager');
            });
        })
            ->when($event->roles && in_array('Vendor', $event->roles), function ($query) use ($event) {
                $query->orWhere(function ($query) use ($event) {
                    $query->where('users.organization_id', $event->organization_id)
                        ->where('users.role', 'Vendor');
                });
            })
            ->when($event->roles && in_array('Principal', $event->roles), function ($query) use ($event) {
                $query->orWhere(function ($query) use ($event) {
                    $query->where('users.organization_id', $event->organization_id)
                        ->whereIn('users.role', ['Principal'])
                        ->when($event->groups && count($event->groups), function ($query) use ($event) {
                            $query->whereIn('users.id', function ($query) use ($event) {
                                $query->select('user_id')
                                    ->from('group_user')
                                    ->whereIn('group_user.group_id', $event->groups);
                            });
                        });
                });
            })
            ->when($event->roles && in_array('User', $event->roles), function ($query) use ($event) {
                $query->orWhere(function ($query) use ($event) {
                    $query->where('users.role', 'Parent');

                    $query->whereIn('users.id', function ($query) use ($event) {
                        $query->when($event->groups && count($event->groups), function ($query) use ($event) {
                            $query->select('parent_child.parent_id')
                                ->from('parent_child')
                                ->whereIn('parent_child.child_id', function ($query) use ($event) {
                                    $query->select('group_user.user_id')
                                        ->from('group_user')
                                        ->whereIn('group_user.group_id', $event->groups)
                                        ->join('groups', 'groups.id', 'group_user.group_id')
                                        ->where('groups.organization_id', $event->organization_id);
                                });
                        }, function ($query) use ($event) {
                            $query->select('parent_child.parent_id')
                                ->from('parent_child')
                                ->whereIn('parent_child.child_id', function ($query) use ($event) {
                                    $query->select('u1.id')
                                        ->from('users as u1')
                                        ->where('u1.role', 'User')
                                        ->where('u1.organization_id', $event->organization_id);
                                });
                        });
                    });
                });
            });
    }
}
