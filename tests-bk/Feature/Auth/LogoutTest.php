<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authenticated_user_can_log_out()
    {
        $user = User::factory()->create([
            'organization_id' => null,
        ]);
        $this->be($user);

        $this->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', trans('auth.logout'));

        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function an_unauthenticated_user_can_not_log_out()
    {
        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertFalse(Auth::check());
    }
}
