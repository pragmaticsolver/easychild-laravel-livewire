<?php

namespace App\Listeners;

use App\Events\ScheduleUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckIfAutoAbsentDone implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ScheduleUpdated $event)
    {
        if (! $this->isLeaveAutoTriggred($event->options)) {
            return;
        }
    }

    private function isLeaveAutoTriggred($options)
    {
        if ($options['type'] == 'leave' && $options['trigger_type'] == 'auto') {
            return true;
        }

        return false;
    }
}
