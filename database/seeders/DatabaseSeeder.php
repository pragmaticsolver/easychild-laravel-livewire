<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('optimize:clear');

        // $this->call(OrganizationSeeder::class);
        // $this->call(GroupSeeder::class);
        // $this->call(UsersSeeder::class);
        // $this->call(GroupUserSeeder::class);
        // $this->call(ScheduleSeeder::class);

        $this->call(ProductionDatabaseSeeder::class);
        $this->call(ParentChildSeeder::class);
    }
}
