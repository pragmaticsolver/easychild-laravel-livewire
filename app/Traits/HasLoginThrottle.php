<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

trait HasLoginThrottle
{
    protected $maxAttempts = 5;
    protected $lockoutMinutes = 1;

    private function checkForTooManyLoginAttempts(User $user = null)
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($user), $this->maxAttempts)) {
            return true;
        }

        return false;
    }

    private function sendLoginView()
    {
        $title = trans('auth.login_title');

        return view('pages.guest', [
            'livewire' => 'auth.login',
            'title' => $title,
            'showAuthHeader' => true,
        ]);
    }

    private function sendIpBannedView()
    {
        $seconds = RateLimiter::availableIn($this->throttleKey());
        session()->flash('message', trans('auth.throttle', ['seconds' => $seconds]));

        return view('auth.ipban');
    }

    private function checkTokenBasedLogin($token)
    {
        $user = User::where('token', $token)->first();

        if ($user && ($user->isParent() || $user->isPrincipal())) {
            if ($this->checkForTooManyLoginAttempts($user)) {
                return $this->sendIpBannedView();
            }

            $this->clearLoginAttempts($user);

            auth()->login($user);

            session()->flash('success', trans('auth.successful_token_login', ['fullname' => auth()->user()->full_name]));

            return redirect(route('dashboard'));
        }

        // add login attempts
        $this->addHit();

        return $this->sendLoginView();
    }

    private function addHit(User $user = null)
    {
        RateLimiter::hit($this->throttleKey($user), 60 * $this->lockoutMinutes);
    }

    private function clearLoginAttempts(User $user = null)
    {
        RateLimiter::clear($this->throttleKey());

        if ($user) {
            RateLimiter::clear($this->throttleKey($user));
        }
    }

    private function throttleKey(User $user = null)
    {
        $userAgent = sha1(request()->userAgent());

        if ($user) {
            return Str::lower($user->email ?: $user->username).$userAgent.'|'.request()->ip();
        }

        return Str::lower(request('email')).$userAgent.'|'.request()->ip();
    }
}
