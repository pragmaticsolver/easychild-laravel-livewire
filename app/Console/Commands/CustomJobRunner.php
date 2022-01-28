<?php

namespace App\Console\Commands;

use App\Models\CustomJob;
use App\Models\User;
use App\Notifications\AttendanceNotification;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CustomJobRunner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:custom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for all notification job due to run and execute them.';

    protected $notificationsMap = [
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CustomJob::chunk(10, function ($jobs) {
            foreach ($jobs as $job) {
                if (now() > $job->due_at) {
                    try {
                        $this->info('Working on custom job: '.$job->id);

                        if ($job->related) {
                            $actions = [
                                'App\\Actions' => 'runAction',
                                'App\\Notifications' => 'sendNotification',
                            ];

                            foreach ($actions as $action => $method) {
                                if (Str::startsWith($job->action, $action)) {
                                    $this->$method($job);
                                }
                            }

                            $job->delete();
                        } else {
                            $job->delete();
                        }
                    } catch (Exception $e) {
                        $this->info($e->getMessage());
                    }
                }
            }
        });

        return 0;
    }

    private function runAction($job)
    {
        app($job->action)->handle($job->related, $job->data);
    }

    private function sendNotification($job)
    {
        $users = User::query()
            ->whereIn('id', $job->user_ids)
            ->get();

        if ($job->action == AttendanceNotification::class) {
            $job->related->relatedNotifications()
                ->delete();
        }

        Notification::send($users, new $job->action($job->related, $job->data));
    }
}
