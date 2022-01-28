<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\Schedule;
use App\Services\OrganizationSchedules;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class UserDailySchedulePersist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule-persist:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save daily users schedule to database.';

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
        $this->info('Running schedule save for ' . $date);
        $this->info('====================================');

        if (! now()->isWeekday()) {
            $this->info('Sorry, its not weekday');

            return;
        }

        Organization::chunk(5, function ($organizations) use ($date) {
            foreach ($organizations as $org) {
                $this->info('Saving schedule for ' . $org->name);

                $schedules = (new OrganizationSchedules())
                    ->setDate($date)
                    ->setOrganization($org)
                    ->fetch();

                foreach ($schedules as $schedule) {
                    if (! $schedule['uuid']) {
                        Schedule::create([
                            'user_id' => $schedule['user_id'],
                            'date' => $schedule['date'],
                            'start' => Arr::has($schedule, 'start') ? $schedule['start'] : null,
                            'end' => Arr::has($schedule, 'end') ? $schedule['end'] : null,
                            'eats_onsite' => $schedule['eats_onsite'],
                            'available' => true,
                            'status' => $schedule['status'],
                            'allergy' => $schedule['allergy'],
                        ]);
                    }
                }
            }
        });
    }
}
