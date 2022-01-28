<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class PasswordRequest extends Component
{
    public $email;

    public function resetPassword()
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $this->email)->first();

        if ($user) {
            Password::sendResetLink([
                'email' => $this->email,
            ]);
        }

        session()->flash('success', trans('auth.password_reset_request_success'));

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.password-request');
    }
}
