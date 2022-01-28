<?php

namespace Database\Seeders;

use App\Models\ParentLink;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParentChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->times(5)->create([
            'role' => 'Parent',
            'organization_id' => null,
        ]);

        $parents = User::query()
            ->where('role', 'Parent')
            ->get();

        $now = now()->format('Y-m-d');
        foreach ($parents as $parent) {
            $childrens = User::query()
                ->where('role', 'User')
                ->pluck('id')
                ->random(3)
                ->all();

            $parentLinks = [];
            foreach ($childrens as $child) {
                $parentLinks[] = [
                    'email' => $parent->email,
                    'child_id' => $child,
                    'token' => Str::random(),
                    'linked' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            ParentLink::insert($parentLinks);
            $parent->childrens()->sync($childrens);
        }
    }
}
