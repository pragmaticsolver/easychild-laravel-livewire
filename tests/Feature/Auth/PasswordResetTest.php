<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function password_reset_request_page_is_accessible()
    {
        $this->get(route('password.request'))
            ->assertSuccessful()
            ->assertSeeLivewire('auth.password-request');
    }

    /** @test */
    public function email_is_not_sent_if_user_not_found_in_system()
    {
        Notification::fake();

        $user = User::factory()->make([
            'organization_id' => null,
        ]);

        Livewire::test('auth.password-request')
            ->set('email', $user->email)
            ->call('resetPassword')
            ->assertRedirect(route('login'))
            ->assertSessionHas(['success' => trans('auth.password_reset_request_success')]);

        Notification::assertNothingSent();
    }

    /** @test */
    public function email_is_sent_when_user_resets_password()
    {
        Notification::fake();

        $user = User::factory()->create([
            'organization_id' => null,
        ]);

        Livewire::test('auth.password-request')
            ->set('email', $user->email)
            ->call('resetPassword')
            ->assertRedirect(route('login'))
            ->assertSessionHas(['success' => trans('auth.password_reset_request_success')]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /** @test */
    public function password_reset_page_has_validation_error()
    {
        $token = '123456';

        Livewire::test('auth.password-reset', compact('token'))
            ->set('email', '')
            ->call('resetPassword')
            ->assertHasErrors(['email' => 'required'])
            ->set('password', '')
            ->call('resetPassword')
            ->assertHasErrors(['password' => 'required'])
            ->set('password', '123')
            ->set('password_confirmation', '')
            ->call('resetPassword')
            ->assertHasErrors(['password' => 'confirmed'])
            ->set('email', 'email@email.com')
            ->set('email', 'email@')
            ->call('resetPassword')
            ->assertHasErrors(['email' => 'email'])

            ->set('password', '1234567891112')
            ->set('password_confirmation', '1234567891112')
            ->call('resetPassword')
            ->assertHasErrors(['password']) // error for symbol
            ->set('password', '$$$$$$$$$$$$A')
            ->set('password_confirmation', '$$$$$$$$$$$$A')
            ->call('resetPassword')
            ->assertHasErrors(['password']) // error for lower character
            ->set('password', '$$$$$$$$$$$$a')
            ->set('password_confirmation', '$$$$$$$$$$$$a')
            ->call('resetPassword')
            ->assertHasErrors(['password']) // error for upper character
            ->set('password', 'abcdefghijklmnop$')
            ->set('password_confirmation', 'abcdefghijklmnop$')
            ->call('resetPassword')
            ->assertHasErrors(['password']); // error for number
    }

    /** @test */
    public function password_reset_is_successfull()
    {
        $user = User::factory()->create([
            'organization_id' => null,
        ]);

        $token = Str::random(60);
        $email = $user->email;

        DB::table('password_resets')
            ->insert([
                'email' => $user->email,
                'token' => bcrypt($token),
            ]);

        $newPassword = '12345678$abcD';

        Livewire::test('auth.password-reset', compact('token', 'email'))
            ->set('password', $newPassword)
            ->set('password_confirmation', $newPassword)
            ->call('resetPassword')
            ->assertRedirect(route('login'))
            ->assertSessionHas(['success' => trans('auth.password_reset_success')]);

        $refreshUser = User::first();
        $passwordReset = Hash::check($newPassword, $refreshUser->password);

        $this->assertTrue($passwordReset);
    }
}
