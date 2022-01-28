<?php

namespace App\Models;

use App\CustomNotification\DatabaseNotificationModel;
use App\Notifications\AttendanceNotification;
use App\Notifications\UserAttendanceDealedNotification;
use App\Services\OrganizationSchedules;
use App\Traits\Uuidable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Schedule extends Model
{
    use Uuidable, HasFactory;

    protected $guarded = [];

    // protected $appends = [
    //     'eats_onsite_breakfast',
    //     'eats_onsite_lunch',
    //     'eats_onsite_dinner',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->morphMany(UserLog::class, 'typeable');
    }

    public function dealer()
    {
        return $this->hasOne(User::class, 'id', 'dealt_by');
    }

    public function relatedNotifications()
    {
        return $this->morphMany(DatabaseNotificationModel::class, 'related')
            ->where('type', AttendanceNotification::class);
    }

    // public function attendances()
    // {
    //     return $this->hasMany(Attendance::class);
    // }

    protected $casts = [
        'available' => 'boolean',
        'eats_onsite' => 'array',
        'current_approved' => 'array',
        // 'eats_onsite_breakfast' => 'boolean',
        // 'eats_onsite_lunch' => 'boolean',
        // 'eats_onsite_dinner' => 'boolean',
        'user_id' => 'integer',
    ];

    public function getDealtAlreadyAttribute()
    {
        $name = $this->last_dealt_name;

        if ($this->dealt_by == auth()->id()) {
            $name = trans('messages.thread.you_title');
        }

        $description = trans('schedules.notification.already_approved_or_rejected', ['name' => $name]);

        return $description;
    }

    public function dealtTitle($userName)
    {
        $dealtBy = $this->last_dealt_name;

        if ($this->dealt_by == auth()->id()) {
            $dealtBy = trans('messages.thread.you_title');
        }

        $description = trans('schedules.notification.schedule_approved_already', [
            'user' => $userName,
            'approver' => $dealtBy,
        ]);

        if ($this->status == 'declined') {
            $description = trans('schedules.notification.schedule_rejected_already', [
                'user' => $userName,
                'approver' => $dealtBy,
            ]);
        }

        return $description;
    }

    public function getApprovalDescriptionAttribute()
    {
        $scheduleDate = Carbon::parse($this->date);

        $scheduledData = [
            'day' => $scheduleDate->dayName,
            'date' => $scheduleDate->format('d'),
            'month' => $scheduleDate->monthName,
            'cause' => $this->check_out ? trans('schedules.check_out.execuses.'.$this->check_out) : null,
        ];

        if (! $this->available && $this->check_out) {
            $description = trans('schedules.notification.detail_checkout', $scheduledData);
        } else {
            if ($this->start && $this->end) {
                $scheduledData['start'] = Carbon::parse($this->start)->format('H:i');
                $scheduledData['end'] = Carbon::parse($this->end)->format('H:i');

                $description = trans('schedules.notification.detail_with_time', $scheduledData);
            } else {
                $description = trans('schedules.notification.detail_make_un_available', $scheduledData);

                if ($this->available) {
                    $description = trans('schedules.notification.detail_make_available', $scheduledData);
                }
            }
        }

        return $description;
    }

    public function sendDealtNotificationToUser($status)
    {
        $this->update([
            'status' => $status,
            'last_dealt_at' => now(),
            'dealt_by' => auth()->id(),
            'last_dealt_name' => auth()->user()->given_names,
            'current_approved' => $status == 'approved' ? null : $this->current_approved,
        ]);

        DatabaseNotificationModel::query()
            ->where('type', UserAttendanceDealedNotification::class)
            ->whereHasMorph('related', self::class)
            ->where('related_id', $this->id)
            ->delete();

        $parentIds = $this->user->parents->pluck('id')->all();
        auth()->user()->jobs()->updateOrCreate([
            'related_type' => self::class,
            'related_id' => $this->id,
            'action' => UserAttendanceDealedNotification::class,
        ], [
            'user_ids' => $parentIds,
            'due_at' => now()->addMinutes(2),
            'data' => [],
        ]);
    }

    public static function todaysSchedule($showAll = false, $date = null)
    {
        $user = auth()->user();
        $group = null;

        if ($user->isManager()) {
            $group = $user->organization;
        } else {
            $group = $user->principal_current_group;
        }

        $orgSchedules = new OrganizationSchedules(true);

        if ($user->isPrincipal()) {
            $orgSchedules->setGroup($group);
        }

        if ($date) {
            $orgSchedules->setDate($date);
        }

        $schedules = $orgSchedules->setOrganization($user->organization)
            ->fetch();

        if (! $showAll) {
            $schedules = $schedules->where('available', true);
        }

        return [$schedules, $group];
    }

    public function getPresenceDiffAttribute()
    {
        $value = 0;

        if ($this->presence_start && $this->presence_end) {
            $start = now()->setTimeFromTimeString($this->presence_start);
            $end = now()->setTimeFromTimeString($this->presence_end);

            return $end->floatDiffInHours($start);
        }

        return $value;
    }

    // public function getEatsOnsiteBreakfastAttribute()
    // {
    //     return $this->eatsOnsiteGetter('breakfast');
    // }

    // public function getEatsOnsiteLunchAttribute()
    // {
    //     return $this->eatsOnsiteGetter('lunch');
    // }

    // public function getEatsOnsiteDinnerAttribute()
    // {
    //     return $this->eatsOnsiteGetter('dinner');
    // }

    // public function setEatsOnsiteBreakfastAttribute($value)
    // {
    //     $this->eatsOnsiteSetter('breakfast', $value);
    // }

    // public function setEatsOnsiteLunchAttribute($value)
    // {
    //     $this->eatsOnsiteSetter('lunch', $value);
    // }

    // public function setEatsOnsiteDinnerAttribute($value)
    // {
    //     $this->eatsOnsiteSetter('dinner', $value);
    // }

    // public function eatsOnsiteGetter($type)
    // {
    //     $setting = $this->eats_onsite;
    //     if (! $setting) {
    //         $setting = [];
    //     }

    //     if (Arr::has($setting, $type)) {
    //         return $setting[$type];
    //     }
    // }

    // public function eatsOnsiteSetter($type, $value)
    // {
    //     $setting = $this->setting;
    //     if (! $setting) {
    //         $setting = [];
    //     }

    //     $setting['eats_onsite'][$type] = $value;

    //     $this->attributes['settings'] = json_encode($setting);
    // }
}
