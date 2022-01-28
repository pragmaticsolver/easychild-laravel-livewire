<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductionDatabaseSeeder extends Seeder
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
            // 'avatar' => '5A7J7nkx8yHyZmwsumBIcyRPDj7xc0NWxfTHCKOj.png',
        ]);

        $org = Organization::factory()->create([
            'name' => 'Kita "Colorando"',
            'street' => 'Klempnerweg',
            'house_no' => '26',
            'zip_code' => '08340',
            'city' => 'Schwarzenberg',
            'settings' => [
                'eats_onsite' => [
                    'breakfast' => true,
                    'lunch' => true,
                    'dinner' => true,
                ],
                'availability' => 'available',
                'limitations' => [
                    'lead_time' => 1,
                    'selection_time' => 14,
                ],
                'opening_times' => [
                    [
                        'key' => 0,
                        'start' => '06:00',
                        'end' => '18:00',
                    ],
                    [
                        'key' => 1,
                        'start' => '06:00',
                        'end' => '18:00',
                    ],
                    [
                        'key' => 2,
                        'start' => '06:00',
                        'end' => '18:00',
                    ],
                    [
                        'key' => 3,
                        'start' => '06:00',
                        'end' => '18:00',
                    ],
                    [
                        'key' => 4,
                        'start' => '06:00',
                        'end' => '18:00',
                    ],
                ],
            ],
        ]);

        Contract::create([
            'title' => 'Vertrag 4h',
            'time_per_day' => 4,
            'overtime' => 10,
            'bring_until' => '09:00',
            'collect_until' => '14:00',
            'organization_id' => $org->id,
        ]);

        Contract::create([
            'title' => 'Vertrag 9h',
            'time_per_day' => 7,
            'overtime' => 10,
            'bring_until' => '08:00',
            'collect_until' => '17:00',
            'organization_id' => $org->id,
        ]);

        $users = [
            [
                'given_names' => 'Stefanie',
                'last_name' => 'Plügge',
                'role' => 'Manager',
                'email' => 'stefanie.pluegge@colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'settings' => [
                    'mail' => true,
                ],
            ],
            [
                'given_names' => 'Tim',
                'last_name' => 'Müller',
                'role' => 'User',
                'email' => 'tim.mueller@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'mail' => true,
                    'allergies' => 'Glutenunverträglichkeit',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => null,
                        'lunch' => null,
                        'dinner' => false,
                    ],
                    'availability' => 'available',
                ],
            ],
            [
                'given_names' => 'Lisa',
                'last_name' => 'Schreiter',
                'role' => 'User',
                'email' => 'lisa.schreiter@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'mail' => true,
                    'allergies' => '',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => false,
                        'lunch' => false,
                        'dinner' => false,
                    ],
                    'availability' => 'available',
                ],
            ],
            [
                'given_names' => 'Felix',
                'last_name' => 'Zimmer',
                'role' => 'User',
                'email' => 'felix.zimmer@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'mail' => true,
                    'allergies' => '',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => null,
                        'lunch' => null,
                        'dinner' => null,
                    ],
                    'availability' => 'not-available-with-time',
                ],
            ],
            [
                'given_names' => 'Mia',
                'last_name' => 'Meier',
                'role' => 'User',
                'email' => 'mia.meier@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'mail' => true,
                    'allergies' => '',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => null,
                        'lunch' => null,
                        'dinner' => null,
                    ],
                    'availability' => 'available',
                ],
            ],
            [
                'given_names' => 'Ursel',
                'last_name' => 'Schmidt',
                'role' => 'Principal',
                'email' => 'ursel.schmidt@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
            ],
            [
                'given_names' => 'Hilde',
                'last_name' => 'Dietrich',
                'role' => 'Principal',
                'email' => 'hilde.dietrich@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'settings' => ['mail' => false],
            ],
            [
                'given_names' => 'Hannelore',
                'last_name' => 'Escher',
                'role' => 'Principal',
                'email' => 'hannelore.escher@b.colorano.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
            ],
            [
                'given_names' => 'Heiko',
                'last_name' => 'Fischer',
                'role' => 'Vendor',
                'email' => 'heiko.fischer@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'settings' => ['mail' => false],
            ],
            [
                'given_names' => 'Moritz',
                'last_name' => 'Meyer',
                'role' => 'User',
                'email' => 'moritz.meyer@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'mail' => true,
                    'allergies' => 'Lackdose-Intoleranz',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => false,
                        'lunch' => false,
                        'dinner' => false,
                    ],
                    'availability' => 'available',
                ],
            ],
            [
                'given_names' => 'Claudius',
                'last_name' => 'Hartmann',
                'role' => 'Manager',
                'email' => 'claudius.hartmann@gmx.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
            ],
            [
                'given_names' => 'Claudius',
                'last_name' => 'Hartmann',
                'role' => 'User',
                'email' => 'claudius.hartmann@web.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'allergies' => 'Tafelkreide',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => false,
                        'lunch' => false,
                        'dinner' => false,
                    ],
                    'availability' => 'available',
                ],
            ],
            [
                'given_names' => 'Helene',
                'last_name' => 'Fischer',
                'role' => 'User',
                'email' => 'helene.fischer@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'allergies' => 'Lactose',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => null,
                        'lunch' => null,
                        'dinner' => null,
                    ],
                    'availability' => 'available',
                ],
            ],
            [
                'given_names' => 'Theo',
                'last_name' => 'Lehmann',
                'role' => 'User',
                'email' => 'theo.lehmann@b.colorando.de',
                'organization_id' => $org->id,
                'password' => bcrypt('password'),
                'contract_id' => random_int(1, 2),
                'settings' => [
                    'allergies' => '',
                    'attendance_token' => Str::random(32),
                    'eats_onsite' => [
                        'breakfast' => null,
                        'lunch' => null,
                        'dinner' => null,
                    ],
                    'availability' => 'not-available-with-time',
                ],
            ],
        ];

        foreach ($users as $user) {
            User::factory()->create($user);
        }

        $groups = [
            [
                'name' => 'Füchse',
                'organization_id' => $org->id,
                'sync' => [
                    'tim.mueller@b.colorando.de',
                    'ursel.schmidt@b.colorando.de',
                    'hilde.dietrich@b.colorando.de',
                    'hannelore.escher@b.colorano.de',
                    'moritz.meyer@b.colorando.de',
                ],
            ],
            [
                'name' => 'Schmetterlinge',
                'organization_id' => $org->id,
                'sync' => [
                    'lisa.schreiter@b.colorando.de',
                    'mia.meier@b.colorando.de',
                    'ursel.schmidt@b.colorando.de',
                    'hilde.dietrich@b.colorando.de',
                    'claudius.hartmann@web.de',
                    'helene.fischer@b.colorando.de',
                    'theo.lehmann@b.colorando.de',
                ],
            ],
            [
                'name' => 'Käfer',
                'organization_id' => $org->id,
                'sync' => [
                    'felix.zimmer@b.colorando.de',
                    'ursel.schmidt@b.colorando.de',
                    'hilde.dietrich@b.colorando.de',
                    'hannelore.escher@b.colorano.de',
                ],
            ],
        ];

        foreach ($groups as $g) {
            $group = Group::factory()->create([
                'name' => $g['name'],
                'organization_id' => $g['organization_id'],
            ]);

            $users = User::query()
                ->whereIn('email', $g['sync'])
                ->pluck('id')
                ->toArray();
            $group->users()->sync($users);
        }
    }
}
