<?php

namespace App\Models;

use App\CustomNotification\DatabaseNotificationModel;
use App\Notifications\CalendarEventNotification;
use App\Traits\Uuidable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CalendarEvent extends Model
{
    use HasFactory, Uuidable;

    protected $guarded = [];

    protected $casts = [
        'organization_id' => 'integer',
        'creator_id' => 'integer',
        'files' => 'array',
        'groups' => 'array',
        'roles' => 'array',
        'all_day' => 'boolean',
    ];

    protected $dates = [
        'from', 'to',
    ];

    public function getFilesAttribute($value)
    {
        if ($value) {
            return collect(json_decode($value))->map(function ($item) {
                return [
                    'name' => $item,
                    'url' => $this->getFileUrlByName($item),
                    'type' => $this->getFileType($item),
                ];
            });
        }

        return collect();
    }

    public function getFileUrlByName($name)
    {
        if ($name) {
            return URL::signedRoute('private-files', [
                'path' => storage_path('app/events/'.$this->uuid.'/'.$name),
                'uuid' => $this->uuid,
                'model' => 'events',
            ]);
        }

        return $name;
    }

    private function getFileType($file)
    {
        $fileTypes = [
            'pdf' => 'pdf',
            'jpeg' => 'image',
            'jpg' => 'image',
            'png' => 'image',
        ];

        $fileExt = (string) Str::of($file)->afterLast('.');

        if (collect($fileTypes)->keys()->contains($fileExt)) {
            return $fileTypes[$fileExt];
        }

        return 'unknown';
    }

    public function relatedNotifications()
    {
        return $this->morphMany(DatabaseNotificationModel::class, 'related')
            ->where('type', CalendarEventNotification::class);
    }

    public function scopeForICal($query)
    {
        $query->where(function ($query) {
            $query->where('from', '>=', now()->startOfDay())
                ->orWhere('to', '>=', now()->startOfDay());
        })->orderBy('created_at');
    }

    public function scopeWithStartAndEndPeriod($query, $start, $end)
    {
        $query->where(function ($query) use ($start, $end) {
            $query->whereBetween('from', [$start, $end])
                ->orWhereBetween('to', [$start, $end]);
        })->orderBy('created_at', 'DESC');
    }

    public function birthdayUser()
    {
        return $this->hasOne(User::class, 'id', 'birthday_id');
    }

    public function scopeForUser($query, User $user = null)
    {
        if (! $user) {
            $user = auth()->user();
        }

        $query->when(in_array($user->role, ['Manager']), function ($query) use ($user) {
            $query->where('organization_id', $user->organization_id);
        })
            ->when(in_array($user->role, ['Vendor']), function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
                $query->whereJsonContains('roles', 'Vendor');
            })
            ->when(in_array($user->role, ['Principal']), function ($query) use ($user) {
                $query->whereJsonContains('roles', 'Principal');
                $query->where('organization_id', $user->organization_id);
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

                $query->where(function ($query) use ($user, $orgs) {
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
                            ->whereJsonContains('roles', 'User')
                            ->where('birthday', false)
                            ->where(function ($query) use ($groups) {
                                $query->whereNull('groups')
                                    ->orWhereJsonLength('groups', 0);

                                if (count($groups)) {
                                    foreach ($groups as $g) {
                                        $query->orWhereJsonContains('groups', $g);
                                    }
                                }
                            });
                    })->orWhere(function ($query) use ($user, $orgs) {
                        $query->whereIn('organization_id', $orgs)
                            ->where('birthday', true)
                            ->whereIn('birthday_id', function ($query) use ($user) {
                                $query->select('parent_child.child_id')
                                    ->from('parent_child')
                                    ->where('parent_child.parent_id', $user->id);
                            });
                    });
                });
            });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
