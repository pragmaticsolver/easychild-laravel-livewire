<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::query()
            ->where('organization_id', 1)
            ->where('role', 'User')->chunk(50, function ($users) {
                $schedules = [];

                foreach ($users as $user) {
                    for ($x = 0; $x < 7; $x++) {
                        $now = now()->addDays($x);

                        if ($now->isWeekday()) {
                            $schedules[] = Schedule::factory()->make([
                                'user_id' => $user->id,
                                'date' => $now->format('Y-m-d'),
                            ])->toArray();
                        }
                    }
                }

                Schedule::insert($schedules);
            });
    }
}
