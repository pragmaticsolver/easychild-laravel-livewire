<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Organization::chunk(5, function ($orgs) {
            $groupsItem = [];

            foreach ($orgs as $org) {
                $users = $org->users;
                $groups = $org->groups->pluck('id')->toArray();

                foreach ($users as $user) {
                    if ($user->isPrincipal()) {
                        foreach ($groups as $group) {
                            $groupsItem[] = [
                                'group_id' => $group,
                                'user_id' => $user->id,
                            ];
                        }
                    }

                    if ($user->isUser()) {
                        $randGroup = $groups[array_rand($groups)];
                        $groupsItem[] = [
                            'group_id' => $randGroup,
                            'user_id' => $user->id,
                        ];
                    }
                }
            }

            DB::table('group_user')->insert($groupsItem);
        });
    }
}
