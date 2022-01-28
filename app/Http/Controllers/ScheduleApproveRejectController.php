<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class ScheduleApproveRejectController extends Controller
{
    public function askConfirmation(Schedule $schedule)
    {
        if ($schedule->last_dealt_at && $schedule->dealt_by) {
            return view('pages.success-confirmation', [
                'title' => config('app.name'),
                'message' => trans('schedules.notification.already_approved_or_rejected', ['name' => $schedule->dealer->given_names]),
            ]);
        }

        $description = $schedule->approval_description;

        return view('pages.ask-confirmation', [
            'title' => trans('schedules.notification.title', ['name' => $schedule->user->given_names]),
            'description' => $description,
            'schedule' => $schedule,
            'approveUrl' => URL::temporarySignedRoute('signed.schedule.approval', now()->addMinutes(5), [
                'schedule' => $schedule->uuid,
                'dealer' => request('dealer'),
                'type' => 'approve',
            ]),
            'rejectUrl' => URL::temporarySignedRoute('signed.schedule.approval', now()->addMinutes(5), [
                'schedule' => $schedule->uuid,
                'dealer' => request('dealer'),
                'type' => 'reject',
            ]),
        ]);
    }

    public function approval(Schedule $schedule, $type)
    {
        $message = trans('schedules.notification.approve_success');
        $status = 'approved';

        if ($type == 'reject') {
            $message = trans('schedules.notification.reject_success');
            $status = 'declined';
        }

        $dealer = User::find(request('dealer'));

        $schedule->update([
            'status' => $status,
            'dealt_by' => $dealer->id,
            'last_dealt_at' => now(),
            'last_dealt_name' => $dealer->given_names,
        ]);

        $schedule->relatedNotifications()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return view('pages.success-confirmation', [
            'title' => config('app.name'),
            'message' => $message,
        ]);
    }
}
