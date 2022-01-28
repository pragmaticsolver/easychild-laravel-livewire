<?php

namespace App\Console\Commands;

use App\Models\UserLog;
use Illuminate\Console\Command;

class ClearOldUserLogDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear user logs older than 6 months';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UserLog::query()
            ->where('created_at', '<', now()->subMonths(6))
            ->delete();
    }
}
