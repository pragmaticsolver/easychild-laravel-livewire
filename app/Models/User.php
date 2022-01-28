<?php

namespace App\Models;

use App\CustomNotification\DatabaseNotificationModel;
use App\Notifications\MessageNotification;
use App\Notifications\ResetPasswordNotification;
use App\Traits\HasConversationPolicies;
use App\Traits\HasModelLimit;
use App\Traits\HasParentRelation;
use App\Traits\HasPrincipalDashboard;
use App\Traits\HasPrivateConversationCheck;
use App\Traits\HasSearchScope;
use App\Traits\HasUserRolesCheck;
use App\Traits\Uuidable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Lab404\Impersonate\Models\Impersonate;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements CanResetPassword, HasLocalePreference
{
    use Notifiable, HasParentRelation, HasSearchScope, Uuidable, HasModelLimit, HasPushSubscriptions, Impersonate, HasFactory, HasPrivateConversationCheck, HasConversationPolicies, HasPrincipalDashboard, HasUserRolesCheck;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'token', 'settings',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'organization_id' => 'integer',
        'email_verified_at' => 'datetime',
        'dob' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * User search column fields.
     *
     * @var array
     */
    protected $searchConfig = [
        'table' => 'users',
        'cols' => [
            'users.given_names',
            'users.last_name',
            'users.email',
            [
                'select' => 'u1.id',
                'from' => 'users as u1',
                'join' => ['organizations as o1', 'u1.organization_id', 'o1.id'],
                'where' => [
                    'o1.name',
                    'o1.address',
                ],
            ],
            [
                'select' => 'u2.id',
                'from' => 'users as u2',
                'multiJoin' => true,
                'join' => [
                    ['group_user as gu1', 'gu1.user_id', 'u2.id'],
                    ['groups as g2', 'g2.id', 'gu1.group_id'],
                ],
                'where' => [
                    'g2.name',
                ],
            ],
        ],
    ];

    public function canImpersonate()
    {
        return $this->isAdmin() || $this->isContractor();
    }

    public function canBeImpersonated()
    {
        return ! ($this->isAdmin() || $this->isContractor() || $this->isUser());
    }

    /**
     * Appends to user data.
     *
     * @var array
     */
    protected $appends = [
        'full_name',
    ];

    public function getFullNameAttribute()
    {
        $fullName = '';

        if ($this->given_names) {
            $fullName .= $this->given_names;
        }

        if ($this->last_name) {
            $fullName .= ' '.$this->last_name;
        }

        return $fullName;
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ?
            Storage::disk('avatars')->url($this->avatar) :
            'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&color=000000&background=E5E7EB';
    }

    public function setPasswordAttribute($password)
    {
        if ($password) {
            $this->attributes['password'] = Hash::needsRehash($password) ? bcrypt($password) : $password;
        }
    }

    public function getOrganizationAvatarAttribute()
    {
        return $this->organization ? $this->organization->avatar_url : asset('img/easychild.svg');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    public function userGroup()
    {
        return $this->groups->first();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function getCacheKey($type = 'child')
    {
        return 'user-'.$this->id.'-'.$type;
    }

    public function setPrincipalCurrentGroup($groupId)
    {
        $key = $this->getCacheKey('group');
        cache()->set($key, $groupId);
    }

    public function getPrincipalCurrentGroupAttribute()
    {
        $key = $this->getCacheKey('group');
        $group = cache()->get($key);

        if ($group) {
            if ($foundGroup = $this->groups->where('id', $group)->first()) {
                return $foundGroup;
            }
        }

        if ($group = $this->groups->first()) {
            $this->setPrincipalCurrentGroup($group->id);
        }

        return $group;
    }

    public function getPrincipalCurrentGroupIdAttribute()
    {
        return $this->principal_current_group ? $this->principal_current_group->id : null;
    }

    public function setActiveThread($threadId)
    {
        if ($this->isParent()) {
            $child = $this->parent_current_child;
            $key = $child->getCacheKey('active-thread');
            cache()->set($key, $threadId);
        } else {
            $key = $this->getCacheKey('active-thread');
            cache()->set($key, $threadId);
        }
    }

    public function getActiveThreadAttribute()
    {
        $key = $this->getCacheKey('active-thread');
        if ($this->isParent()) {
            $child = $this->parent_current_child;
            $key = $child->getCacheKey('active-thread');
        }

        $activeThread = cache()->get($key);

        if ($activeThread) {
            return (int) $activeThread;
        }

        return false;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function updateConversationNotification($conversationId)
    {
        ConversationNotification::updateOrCreate(
            [
                'user_id' => $this->id,
                'conversation_id' => $conversationId,
            ],
            [
                'read_at' => now(),
            ]
        );

        $this->applyReadAtToNotifications($conversationId);
        $this->checkForCustomJobsStillInQueue($conversationId);
    }

    public function applyReadAtToNotifications($conversationId)
    {
        $this->notifications()
            ->whereHasMorph('related', Conversation::class)
            ->where('type', MessageNotification::class)
            ->where('related_id', $conversationId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    public function checkForCustomJobsStillInQueue($conversationId)
    {
        $customJobs = CustomJob::query()
            ->whereHasMorph('related', Conversation::class)
            ->where('related_id', $conversationId)
            ->where('action', MessageNotification::class)
            ->whereJsonContains('user_ids', $this->id)
            ->get();

        foreach ($customJobs as $job) {
            $job->update([
                'user_ids' => collect($job->user_ids)->filter(function ($value) {
                    return $value != $this->id;
                })->values()->all(),
            ]);
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function notifications()
    {
        return $this->morphMany(DatabaseNotificationModel::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function getMyNotifiableIdList()
    {
        $usersList = [];

        if ($this->isParent()) {
            $usersList = $this->childrens()->pluck('users.id')->all();
        }

        $usersList[] = $this->id;

        return $usersList;
    }

    public function jobs()
    {
        return $this->hasMany(CustomJob::class, 'auth_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function preferredLocale()
    {
        $locale = config('app.locale');

        $settings = $this->settings;
        if (Arr::has($settings, 'lang')) {
            $locale = $settings['lang'];
        }

        return $locale;
    }
}
