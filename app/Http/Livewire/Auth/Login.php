<?php

namespace App\Http\Livewire\Auth;

use App\Http\Livewire\Component;
use App\Models\User;
use App\Traits\HasLoginThrottle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class Login extends Component
{
    use HasLoginThrottle;

    public $email = '';
    public $password = '';
    public $remember = false;

    public function authenticate()
    {
        $this->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        $credentials = [];
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $this->email;
        } else {
            $credentials['username'] = $this->email;
        }

        $credentials['password'] = $this->password;

        $mockedUser = User::make($credentials);
        if (RateLimiter::tooManyAttempts($this->throttleKey($mockedUser), $this->maxAttempts)) {
            return redirect(route('login'));
        }

        if (RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts)) {
            return redirect(route('login'));
        }

        if (! auth()->guard()->attempt($credentials, $this->remember)) {
            $this->addError('email', trans('auth.failed'));

            $this->addHit($mockedUser);
            $this->addHit();

            return;
        }

        if (auth()->user()->isUser()) {
            auth()->logout();

            session()->flash('error', trans('auth.user_login_disabled'));

            return redirect(route('login'));
        }

        $this->clearLoginAttempts($mockedUser);

        session()->flash('success', trans('auth.successful_login', ['fullname' => auth()->user()->full_name]));

        return redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
