<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class LoginTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function can_view_login_page()
    {
        $this->get(route('login'))
            ->assertSuccessful()
            ->assertSeeLivewire('auth.login');
    }

    /** @test */
    public function is_redirected_if_already_logged_in()
    {
        $user = User::factory()->create([
            'organization_id' => null,
        ]);

        $this->be($user);

        $this->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function a_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'organization_id' => null,
        ]);

        $res = Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate');

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function is_redirected_to_the_home_page_after_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'organization_id' => null,
        ]);

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function email_is_required()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'organization_id' => null,
        ]);

        Livewire::test('auth.login')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_must_be_valid_email()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'organization_id' => null,
        ]);

        Livewire::test('auth.login')
            ->set('email', 'invalid-email')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function password_is_required()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'organization_id' => null,
        ]);

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['password' => 'required']);
    }

    /** @test */
    public function bad_login_attempt_shows_message()
    {
        $user = $this->getUser('User');

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'bad-password')
            ->call('authenticate')
            ->assertSeeText(trans('auth.failed'));

        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function bad_login_attempt_after_five_times_show_throttle_error()
    {
        $user = $this->getUser('User');

        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', $user->email)
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $livewire->set('email', $user->email)
            ->set('password', 'bad-password')
            ->call('authenticate')
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            // ->assertSessionHas('message')
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function bad_login_attempt_after_five_times_with_wrong_email_show_throttle_error()
    {
        $user = $this->getUser('User');

        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', 'bademail1@email.com')
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $livewire->set('email', $user->email)
            ->set('password', 'bad-password')
            ->call('authenticate')
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            // ->assertSessionHas('message')
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_wrong_token_pinging_the_server_will_be_banned()
    {
        for ($times = 1; $times <= 5; $times++) {
            $this->get(route('login', ['token' => 'fake-token']));
            // ->assertSessionHas('error', trans('auth.token_error'));
        }

        $this->get(route('login', ['token' => 'fake-token']))
            // ->assertSessionHas('message')
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_correct_token_will_fail_login_if_already_five_failed_attempted_login()
    {
        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', "bademail{$x}@email.com")
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $user = $this->getUser('User');

        $this->get(route('login', ['token' => $user->token]))
            // ->assertSessionHas('message')
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_correct_token_will_fail_login_if_already_five_failed_attempted_login_using_same_email()
    {
        $user = $this->getUser('User');

        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', $user->email)
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $this->json('GET', route('login'), ['token' => $user->token])
            // ->assertSessionHas('message')
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_correct_token_will_be_logged_in()
    {
        $user = $this->getUser('User');

        $this->get(route('login', ['token' => $user->token]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', trans('auth.successful_token_login', ['fullname' => $user->full_name]));

        $this->assertAuthenticatedAs($user);
    }
}
