<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chunk = 5;

        for ($i = 1; $i <= 2; $i++) {
            Organization::factory()->count($chunk)->create();
        }
    }
}
