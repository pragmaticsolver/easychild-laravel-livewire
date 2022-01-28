<?php

namespace App\Models;

use App\CustomNotification\DatabaseNotificationModel;
use App\Notifications\InformationAddedNotification;
use App\Traits\Uuidable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Information extends Model
{
    use HasFactory, Uuidable;

    protected $table = 'informations';

    protected $guarded = [];

    protected $casts = [
        'groups' => 'array',
        'roles' => 'array',
        'organization_id' => 'integer',
        'creator_id' => 'integer',
    ];

    public function scopeWithLastNotification($query, User $user = null)
    {
        if (auth()->check() && ! $user) {
            $user = auth()->user();
        }

        $query->addSelect(['last_notification' => DatabaseNotificationModel::select('read_at')
            ->whereColumn('related_id', 'informations.id')
            ->whereHasMorph('related', self::class)
            ->where('notifiable_id', $user->id)
            ->where('type', InformationAddedNotification::class)
            ->take(1),
        ]);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function BKscopeForUser($query, User $user = null)
    {
        if (auth()->check() && ! $user) {
            $user = auth()->user();
        }

        $query->when($user->isParent(), function ($query) use ($user) {
            $query->whereJsonContains('informations.roles', 'User');

            $query->whereIn('informations.organization_id', function ($query) use ($user) {
                $query->select('users.organization_id')
                    ->from('users')
                    ->where('users.role', 'User')
                    ->whereIn('users.id', function ($query) use ($user) {
                        $query->select('parent_child.child_id')
                            ->from('parent_child')
                            ->where('parent_child.parent_id', $user->id);
                    });
            });
        }, function ($query) use ($user) {
            $query->where('organization_id', $user->organization_id);

            if (! $user->isManager()) {
                $query->whereJsonContains('informations.roles', $user->role);
            }
        });
    }

    public function scopeForUser($query, User $user = null)
    {
        if (auth()->check() && ! $user) {
            $user = auth()->user();
        }

        $query->when($user->isParent(), function ($query) use ($user) {
            $query->whereJsonContains('informations.roles', 'User');

            $query->whereIn('informations.organization_id', function ($query) use ($user) {
                $query->select('users.organization_id')
                    ->from('users')
                    ->where('users.role', 'User')
                    ->whereIn('users.id', function ($query) use ($user) {
                        $query->select('parent_child.child_id')
                            ->from('parent_child')
                            ->where('parent_child.parent_id', $user->id);
                    });
            });
        }, function ($query) use ($user) {
            $query->where('organization_id', $user->organization_id);

            if (! $user->isManager()) {
                $query->whereJsonContains('informations.roles', $user->role);
            }
        });

        $query->when(in_array($user->role, ['Manager']), function ($query) use ($user) {
            $query->where('organization_id', $user->organization_id);
        })
            ->when(in_array($user->role, ['Vendor']), function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id)
                    ->whereJsonContains('roles', 'Vendor');
            })
            ->when(in_array($user->role, ['Principal']), function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id)
                    ->whereJsonContains('roles', 'Principal');
                $query->where(function ($query) use ($user) {
                    $query->whereNull('groups')
                        ->orWhereJsonLength('groups', 0);

                    $groups = $user->groups->pluck('id')->all();

                    if (count($groups)) {
                        foreach ($groups as $g) {
                            $query->orWhereJsonContains('groups', $g);
                        }
                    }
                });
            })
            ->when(in_array($user->role, ['Parent']), function ($query) use ($user) {
                $orgs = Organization::query()
                    ->whereIn('organizations.id', function ($query) use ($user) {
                        $query->select('groups.organization_id')
                            ->from('groups')
                            ->whereIn('groups.id', function ($query) use ($user) {
                                $query->select('group_user.group_id')
                                    ->from('group_user')
                                    ->whereIn('group_user.user_id', function ($query) use ($user) {
                                        $query->select('parent_child.child_id')
                                            ->from('parent_child')
                                            ->where('parent_child.parent_id', $user->id);
                                    });
                            });
                    })->pluck('organizations.id')->toArray();

                $query->whereJsonContains('roles', 'User');
                $query->where(function ($query) use ($user, $orgs) {
                    $groups = Group::query()
                        ->whereIn('groups.id', function ($query) use ($user) {
                            $query->select('group_user.group_id')
                                ->from('group_user')
                                ->whereIn('group_user.user_id', function ($query) use ($user) {
                                    $query->select('parent_child.child_id')
                                        ->from('parent_child')
                                        ->where('parent_child.parent_id', $user->id);
                                });
                        })->pluck('groups.id')->toArray();

                    $query->whereIn('organization_id', $orgs)
                        ->where(function ($query) use ($groups) {
                            $query->whereNull('groups')
                                ->orWhereJsonLength('groups', 0);

                            if (count($groups)) {
                                foreach ($groups as $g) {
                                    $query->orWhereJsonContains('groups', $g);
                                }
                            }
                        });
                });
            });
    }

    public function getFileObjectAttribute()
    {
        return [
            'type' => 'pdf',
            'name' => $this->file,
            'url' => $this->file_url,
        ];
    }

    public function getFileUrlAttribute()
    {
        if ($this->file) {
            return URL::signedRoute('private-files', [
                'path' => storage_path('app/files/'.$this->uuid.'/'.$this->file),
                'model' => 'information',
                'uuid' => $this->uuid,
            ]);
        }

        return $this->file;
    }
}
