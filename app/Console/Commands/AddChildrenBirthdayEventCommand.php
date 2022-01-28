<?php

namespace App\Console\Commands;

use App\Jobs\ChildrenBirthdayEventJob;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Console\Command;

class AddChildrenBirthdayEventCommand extends Command
{
    protected $signature = 'event:birthday';
    protected $description = 'add/update events for children birthday';

    public function handle()
    {
        Organization::query()
            ->chunk(10, function ($organizations) {
                foreach ($organizations as $organization) {
                    User::query()
                        ->where('organization_id', $organization->id)
                        ->where('role', 'User')
                        ->chunk(10, function ($users) {
                            foreach ($users as $user) {
                                if ($user->dob) {
                                    ChildrenBirthdayEventJob::dispatch($user);
                                }
                            }
                        });
                }
            });

        return 0;
    }
}
