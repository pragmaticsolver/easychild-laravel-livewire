<?php

namespace App\Http\Livewire\Auth;

use App\Rules\PasswordPolicyRule;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Component;

class PasswordReset extends Component
{
    public $token;
    public $email;
    public $password;
    public $password_confirmation;

    public function resetPassword()
    {
        $data = $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                new PasswordPolicyRule,
            ],
        ]);

        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                $user->setRememberToken(Str::random(60));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            session()->flash('success', trans('auth.password_reset_success'));
        } else {
            session()->flash('error', trans('auth.password_reset_error'));
        }

        redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.password-reset');
    }
}
