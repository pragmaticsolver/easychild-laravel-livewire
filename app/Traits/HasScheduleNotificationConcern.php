<?php

namespace App\Traits;

use App\Models\CustomJob;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\AttendanceNotification;
use App\Notifications\UserAttendanceDealedNotification;

trait HasScheduleNotificationConcern
{
    private function removeRelatedNotifications(Schedule $schedule)
    {
        $this->currentChild->jobs()
            ->whereHasMorph('related', Schedule::class)
            ->where('related_id', $schedule->id)
            ->where('action', AttendanceNotification::class)
            ->delete();

        $schedule->relatedNotifications()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);
    }

    private function addNotificationToCustomJob(Schedule $schedule)
    {
        $users = User::query()
            ->whereIn('users.id', function ($query) {
                $query->select('user_id')
                    ->from('group_user')
                    ->whereIn('group_user.group_id', function ($query) {
                        $query->select('group_id')
                            ->from('group_user')
                            ->where('group_user.user_id', $this->currentChild->id);
                    });
            })
            ->where('users.role', 'Principal')
            ->where('users.organization_id', $this->currentChild->organization_id)
            ->pluck('users.id')
            ->toArray();

        if ($users && count($users)) {
            CustomJob::query()
                ->where('related_type', Schedule::class)
                ->where('related_id', $schedule->id)
                ->where('action', UserAttendanceDealedNotification::class)
                ->delete();

            // TODO: set priority for custom job depending on date of schedule

            $this->currentChild->jobs()->updateOrCreate([
                'related_type' => Schedule::class,
                'related_id' => $schedule->id,
                'action' => AttendanceNotification::class,
            ], [
                'user_ids' => $users,
                'due_at' => now()->addMinutes(2),
                'data' => [],
            ]);
        }
    }
}
