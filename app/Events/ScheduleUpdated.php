<?php

namespace App\Events;

use App\Models\Schedule;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $schedule;
    public $options;

    /**
     * Create a new event instance.
     *
     * @param Schedule $schedule
     * @param array $options
     */
    public function __construct(Schedule $schedule, $options = [])
    {
        $this->schedule = $schedule;
        $this->options = $options;
    }
}
