<?php

namespace App\CustomNotification;

use App\Models\User;
use App\Notifications\AttendanceNotification;
use App\Notifications\CalendarEventNotification;
use App\Notifications\InformationAddedNotification;
use App\Notifications\MessageNotification;
use App\Notifications\NoteNotification;
use App\Notifications\UserAttendanceDealedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DatabaseNotificationModel extends Model
{
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    protected $dates = [
        'read_at',
    ];

    protected $possibleComponentsName = [
        InformationAddedNotification::class => 'notifications.information',
        NoteNotification::class => 'notifications.note',
        MessageNotification::class => 'notifications.message',
        AttendanceNotification::class => 'notifications.attendance',
        UserAttendanceDealedNotification::class => 'notifications.attendance-dealed',
        CalendarEventNotification::class => 'notifications.calendar-event',
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read()
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread()
    {
        return $this->read_at === null;
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRead(Builder $query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread(Builder $query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRelatedTypes(Builder $query, $types)
    {
        return $query->whereHasMorph('related', $types);
    }

    public function getBladeComponentAttribute()
    {
        return $this->possibleComponentsName[$this->type];
    }

    public function scopeUserNotification($query)
    {
        $usersList = auth()->user()->getMyNotifiableIdList();

        $query->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', $usersList)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Create a new database notification collection instance.
     *
     * @param  array  $models
     * @return \App\CustomNotification\DatabaseNotificationCollection
     */
    public function newCollection(array $models = [])
    {
        return new DatabaseNotificationCollection($models);
    }
}
