<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\ScheduleReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;

class RemindUserToCreateScheduleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remind:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind to parent to create schedule.';

    private $notifications = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Working on schedule reminder: '.now()->format('Y-m-d'));

        Organization::chunk(5, function ($organizations) {
            foreach ($organizations as $organization) {
                $orgSettings = $organization->settings;
                $leadTime = $orgSettings['limitations']['lead_time'];

                $isAvailableByDefault = $orgSettings['availability'] == 'available';
                $userNeedsTime = $orgSettings['availability'] == 'not-available-with-time';
                $lastRemindDay = now()->addDays($leadTime);

                if ($lastRemindDay->isSameDay(now())) {
                    $lastRemindDay->addDay();
                }

                while ($lastRemindDay->isWeekend()) {
                    $lastRemindDay->addDay();
                }

                $startDay = $lastRemindDay->copy();
                $endDay = $lastRemindDay->copy();

                for ($i = 1; $i < 2; $i++) {
                    $endDay->addDay();

                    while ($endDay->isWeekend()) {
                        $endDay->addDay();
                    }
                }

                $this->info('Working on organization '.$organization->name);
                // $this->info('Working on organization ' . $organization->name . ' available: ' . $orgSettings['availability'] . ' lead time: ' . $leadTime . ' remind day: ' . $lastRemindDay->format('Y-m-d'));

                User::query()
                    ->where('organization_id', $organization->id)
                    ->whereIn('role', ['User'])
                    ->chunk(50, function ($users) use ($startDay, $endDay, $isAvailableByDefault, $userNeedsTime) {
                        foreach ($users as $user) {
                            $userSetting = $user->settings;
                            $isUserAvailable = $isAvailableByDefault;

                            if (Arr::has($userSetting, 'availability')) {
                                $isUserAvailable = $userSetting['availability'] == 'available';
                                $userNeedsTime = $userSetting['availability'] == 'not-available-with-time';
                            }

                            if (! $isUserAvailable) {
                                $schedulesCount = Schedule::query()
                                    ->where('user_id', $user->id)
                                    ->where('date', '>=', $startDay->format('Y-m-d'))
                                    ->where('date', '<=', $endDay->format('Y-m-d'))
                                    ->when($userNeedsTime, function ($query) {
                                        $query->where(function ($query) {
                                            $query->where('available', false)
                                                ->orWhere(function ($query) {
                                                    $query->whereNotNull('start');
                                                    $query->whereNotNull('end');
                                                });
                                        });
                                    })
                                    ->count();

                                if ($schedulesCount < 2) {
                                    // send notification to all parents of this user
                                    $parents = $user->parents;

                                    foreach ($parents as $parent) {
                                        if (Arr::has($this->notifications, $parent->uuid)) {
                                            $notificationData = $this->notifications[$parent->uuid];
                                            $notificationData['children'][] = $user->given_names;
                                        } else {
                                            $notificationData = [
                                                'children' => [$user->given_names],
                                                'parent' => $parent,
                                            ];
                                        }

                                        $this->notifications[$parent->uuid] = $notificationData;
                                    }
                                }
                            }
                        }
                    });
            }
        });

        $this->sendNotifications();
    }

    private function sendNotifications()
    {
        foreach ($this->notifications as $item) {
            $parent = $item['parent'];
            $parent->notify(new ScheduleReminderNotification($item['children']));
        }
    }
}
