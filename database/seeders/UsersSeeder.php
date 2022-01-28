<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin User
        User::factory()->create([
            'given_names' => 'Colorando',
            'last_name' => 'Admin',
            'email' => 'info@colorando.de',
            'role' => 'Admin',
            'password' => bcrypt('password'),
            'organization_id' => null,
        ]);

        User::factory()->create([
            'given_names' => 'Santosh',
            'last_name' => 'Admin',
            'email' => 'santosh.khanal55@gmail.com',
            'role' => 'Admin',
            'password' => bcrypt('password'),
            'organization_id' => null,
        ]);

        User::factory()->create([
            'given_names' => 'Santosh',
            'last_name' => 'Manager',
            'email' => 'santosh.khanal55@outlook.com',
            'role' => 'Manager',
            'password' => bcrypt('password'),
            'organization_id' => 1,
        ]);

        User::factory()->create([
            'given_names' => 'Santosh',
            'last_name' => 'Principal',
            'email' => 'santosh@principal.com',
            'role' => 'Principal',
            'password' => bcrypt('password'),
            'organization_id' => 1,
        ]);

        $parent = User::factory()->create([
            'given_names' => 'Santosh',
            'last_name' => 'Parent',
            'email' => 'santosh@parent.com',
            'role' => 'Parent',
            'password' => bcrypt('password'),
            'organization_id' => 1,
        ]);

        $chunk = 20;
        $now = now();

        $orgCount = Organization::count();

        for ($i = 1; $i <= $orgCount; $i++) {
            $usersFactory = User::factory()->count($chunk)->make([
                'created_at' => $now,
                'updated_at' => $now,
                'role' => 'User',
                'organization_id' => $i,
            ]);

            $usersList[] = User::factory()->make([
                'created_at' => $now,
                'updated_at' => $now,
                'role' => 'Manager',
                'organization_id' => $i,
            ])->getAttributes();

            $vendor = User::factory()->make([
                'created_at' => $now,
                'updated_at' => $now,
                'email' => "org-{$i}@vendor.com",
                'role' => 'Vendor',
                'organization_id' => $i,
            ]);

            $principalsFactory = User::factory()->count(5)->make([
                'created_at' => $now,
                'updated_at' => $now,
                'role' => 'Principal',
                'organization_id' => $i,
            ]);

            $usersList = [];
            $usersList[] = $vendor->getAttributes();

            foreach ($usersFactory as $user) {
                $usersList[] = $user->getAttributes();
            }

            foreach ($principalsFactory as $user) {
                $usersList[] = $user->getAttributes();
            }

            User::insert($usersList);

            if ($i === 1) {
                $childrens = User::query()
                    ->where('organization_id', $i)
                    ->where('role', 'User')
                    ->take(2)
                    ->inRandomOrder()
                    ->pluck('id')
                    ->toArray();
                $parent->childrens()->sync($childrens);
            }
        }
    }
}
