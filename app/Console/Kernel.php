<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('schedule-persist:daily')
            ->dailyAt('01:00');
        $schedule->command('children-absent:check')
            ->everyThirtyMinutes()
            ->between('06:00', '23:59');

        $schedule->command('schedule-monitor:sync')
            ->dailyAt('04:56');
        $schedule->command('schedule-monitor:clean')
            ->daily();

        $schedule->command('job:custom')
            ->everyMinute();

        $schedule->command('remind:schedule')
            ->dailyAt('19:00');

        $schedule->command('user-logs:clear')
            ->monthly();

        $schedule->command('event:birthday')
            ->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
