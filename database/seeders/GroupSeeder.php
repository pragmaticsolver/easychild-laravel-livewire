<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $orgs = Organization::all();

        foreach ($orgs as $org) {
            Group::factory()->count(5)->create([
                'organization_id' => $org->id,
            ]);
        }
    }
}
