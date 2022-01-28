<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class LoginTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    private $bannedMinute = 1;

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
    public function a_user_cannot_login()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function a_parent_can_login()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();
        $parent = $this->getUser('Parent');
        $parent->childrens()->attach($user->id);

        Livewire::test('auth.login')
            ->set('email', $parent->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($parent);
    }

    /** @test */
    public function a_manager_can_login()
    {
        [$org] = $this->getOrgGroupUser();
        $manager = $this->getUser('Manager', $org->id);

        Livewire::test('auth.login')
            ->set('email', $manager->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($manager);
    }

    /** @test */
    public function a_principal_can_login()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();
        $principal = $this->getUser('Principal', $org->id);

        $principal->groups()->attach($group->id);

        Livewire::test('auth.login')
            ->set('email', $principal->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($principal);
    }

    /** @test */
    public function a_principal_can_login_using_username()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();
        $principal = $this->getUser('Principal', $org->id);
        $principal->update([
            'username' => 'username',
        ]);

        $principal->groups()->attach($group->id);

        Livewire::test('auth.login')
            ->set('email', $principal->username)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($principal);
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
            ->assertHasErrors(['email']);
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
        TestTime::freeze();

        $user = $this->getUser('User');

        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', $user->email)
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $this->get(route('login'))
            ->assertSessionHas('message', trans('auth.throttle', ['seconds' => 60 * $this->bannedMinute]))
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function bad_login_attempt_after_five_times_with_wrong_email_show_throttle_error()
    {
        TestTime::freeze();
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
            ->assertSessionHas('message', trans('auth.throttle', ['seconds' => 60 * $this->bannedMinute]))
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_wrong_token_pinging_the_server_will_be_banned()
    {
        TestTime::freeze();

        for ($times = 1; $times <= 5; $times++) {
            $this->get(route('login', ['token' => 'fake-token']));
        }

        $this->get(route('login', ['token' => 'fake-token']))
            ->assertSessionHas('message', trans('auth.throttle', ['seconds' => 60 * $this->bannedMinute]))
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_correct_token_will_fail_login_if_already_five_failed_attempted_login()
    {
        TestTime::freeze();

        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', "bademail{$x}@email.com")
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $user = $this->getUser('User');

        $this->get(route('login', ['token' => $user->token]))
            ->assertSessionHas('message', trans('auth.throttle', ['seconds' => 60 * $this->bannedMinute]))
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_correct_token_will_fail_login_if_already_five_failed_attempted_login_using_same_email()
    {
        TestTime::freeze();

        $user = $this->getUser('User');

        $livewire = Livewire::test('auth.login');

        for ($x = 0; $x < 5; $x++) {
            $livewire->set('email', $user->email)
                ->set('password', 'bad-password')
                ->call('authenticate');
        }

        $this->json('GET', route('login'), ['token' => $user->token])
            ->assertSessionHas('message', trans('auth.throttle', ['seconds' => 60 * $this->bannedMinute]))
            ->assertSeeText(trans('auth.ip_banned'));
    }

    /** @test */
    public function user_with_correct_token_will_be_logged_in()
    {
        [$org, $group, $user] = $this->getOrgGroupUser('User');
        $parent = $this->getUser('Parent');
        $parent->childrens()->attach($user->id);

        $this->get(route('login', ['token' => $parent->token]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', trans('auth.successful_token_login', ['fullname' => $parent->full_name]));

        $this->assertAuthenticatedAs($parent);
    }
}
