<?php

namespace Tests\Unit;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OrganizationModelTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase;

    /** @test */
    public function organization_has_users_relationship()
    {
        $org = Organization::factory()->create();
        $user = User::factory()->make([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $org->users()->save($user);

        $this->assertTrue($org->users()->first()->is($user));
    }

    /** @test */
    public function organization_return_empty_users_when_no_users_on_organization()
    {
        $org = Organization::factory()->create();

        $this->assertEmpty($org->users);
    }

    /** @test */
    public function organization_can_create_many_users_at_once_using_relationship()
    {
        $org = Organization::factory()->create();
        $org->users()->saveMany(
            User::factory()
                ->count(10)
                ->make([
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
        );

        $this->assertEquals(10, $org->users()->count());
    }
}
