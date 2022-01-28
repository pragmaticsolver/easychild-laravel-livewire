<?php

namespace App\Console\Commands;

use App\Events\ScheduleUpdated;
use App\Models\Organization;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SetUsersToAbsentOnOrganizationClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'children-absent:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and make children absent when institute close';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = now()->format('Y-m-d');

        if (! now()->isWeekday()) {
            $this->info('Sorry, its not weekday');

            return;
        }

        Organization::chunk(5, function ($organizations) use ($date) {
            foreach ($organizations as $organization) {
                $this->info('Working on organization '.$organization->name);
                $openingTimes = $organization->settings['opening_times'];

                $currentWeekDay = Carbon::parse($date)->dayOfWeek - 1;
                $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

                if ($openingTime) {
                    $dateTime = now()->setTimeFromTimeString($openingTime['end']);

                    if (now() >= $dateTime) {
                        $schedules = Schedule::query()
                            ->whereIn('user_id', function ($query) use ($organization) {
                                $query->select('id')
                                    ->from('users')
                                    ->where('organization_id', $organization->id)
                                    ->where('role', 'User');
                            })
                            ->where('date', $date)
                            ->whereNotNull('presence_start')
                            ->whereNull('presence_end')
                            ->get();

                        // Notification about schedules auto logoff

                        foreach ($schedules as $schedule) {
                            $schedule->presence_end = $dateTime->format('H:i');
                            $schedule->save();

                            ScheduleUpdated::dispatch($schedule, [
                                'type' => 'leave',
                                'trigger_type' => 'auto',
                                'triggred_id' => null,
                            ]);
                        }
                    }
                }
            }
        });
    }
}
