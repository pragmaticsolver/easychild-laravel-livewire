<?php

namespace App\Http\Controllers\Api;

use App\Events\ScheduleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Arr;

class AttendLeaveController extends Controller
{
    public function attend($token)
    {
        $user = User::query()
            ->where('settings->attendance_token', $token)
            ->first();

        if ($user) {
            $schedule = Schedule::query()
                ->where('user_id', $user->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();

            if ($schedule) {
                $schedule->update([
                    'available' => true,
                    'status' => 'approved',
                    'presence_start' => now()->format('H:i'),
                    'presence_end' => null,
                ]);
            } else {
                $orgSettings = $user->organization->settings;
                $eatsOnsite = $orgSettings['eats_onsite'];
                if (Arr::has($user->settings, 'eats_onsite')) {
                    $eatsOnsite = $user->settings['eats_onsite'];
                }

                $schedule = Schedule::create([
                    'user_id' => $user->id,
                    'date' => now()->format('Y-m-d'),
                    'available' => true,
                    'status' => 'approved',
                    'presence_start' => now()->format('H:i'),
                    'presence_end' => null,
                    'allergy' => $user->allergy,
                    'eats_onsite' => $eatsOnsite,
                ]);
            }

            ScheduleUpdated::dispatch($schedule, [
                'type' => 'enter',
                'trigger_type' => 'terminal',
                'triggred_id' => null,
            ]);

            return response()->noContent();
        }

        return response()->noContent(422);
    }

    public function leave($token)
    {
        $user = User::query()
            ->where('settings->attendance_token', $token)
            ->first();

        if ($user) {
            $schedule = Schedule::query()
                ->where('user_id', $user->id)
                ->where('date', now()->format('Y-m-d'))
                ->whereNotNull('presence_start')
                ->whereNull('presence_end')
                ->first();

            if ($schedule) {
                $schedule->update([
                    'presence_end' => now()->format('H:i'),
                ]);

                ScheduleUpdated::dispatch($schedule, [
                    'type' => 'leave',
                    'trigger_type' => 'terminal',
                    'triggred_id' => null,
                ]);

                return response()->noContent();
            }
        }

        return response()->noContent(422);
    }
}
