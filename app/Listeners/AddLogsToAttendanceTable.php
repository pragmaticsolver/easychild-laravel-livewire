<?php

namespace App\Listeners;

use App\Events\ScheduleUpdated;
use App\Models\Schedule;
use App\Models\UserLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Queue\InteractsWithQueue;

class AddLogsToAttendanceTable implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Handle the event.
     *
     * @param  ScheduleUpdated  $event
     * @return void
     */
    public function handle(ScheduleUpdated $event)
    {
        if ($event->options['type'] == 'leave') {
            $lastTrigger = UserLog::query()
                ->where('user_id', $event->schedule->user_id)
                ->where('type', 'enter')
                ->whereHasMorph('typeable', [Schedule::class], function ($query) use ($event) {
                    $query->where('typeable_id', $event->schedule->id);
                })->latest()->first();

            if ($lastTrigger && now()->subMinutes(15) < $lastTrigger->created_at) {
                $lastTrigger->delete();

                $event->schedule->update([
                    'presence_start' => null,
                    'presence_end' => null,
                ]);

                return;
            }
        }

        $this->addLog($event);
    }

    private function addLog(ScheduleUpdated $event)
    {
        $data = [
            'user_id' => $event->schedule->user_id,
            'typeable_id' => $event->schedule->id,
            'typeable_type' => Schedule::class,
            'type' => $event->options['type'],
            'trigger_type' => $event->options['trigger_type'],
            'triggred_id' => $event->options['triggred_id'],
            'note' => isset($event->options['note']) ? $event->options['note'] : null
        ];

        if (Arr::has($event->options, 'datetime')) {
            $data['created_at'] = $event->options['datetime'];
            $data['updated_at'] = $event->options['datetime'];
        }

        UserLog::create($data);
    }
}
