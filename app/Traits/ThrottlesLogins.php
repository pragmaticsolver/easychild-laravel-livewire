<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Auth\Events\Lockout;

trait ThrottlesLogins
{
    protected $maxAttempts = 5;

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts($username, $ip)
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey($username, $ip),
            $this->maxAttempts
        );
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function incrementLoginAttempts($username, $ip)
    {
        $this->limiter()->hit(
            $this->throttleKey($username, $ip),
            $this->decayMinutes()
        );
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function getLockoutSeconds($username, $ip)
    {
        return $this->limiter()->availableIn(
            $this->throttleKey($username, $ip)
        );
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearLoginAttempts($username, $ip)
    {
        $this->limiter()->clear($this->throttleKey($username, $ip));
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    // protected function fireLockoutEvent(Request $request)
    // {
    //     event(new Lockout($request));
    // }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey($username, $ip)
    {
        if ($username) {
            return Str::lower($username) . '|' . $ip;
        }

        return $ip;
    }

    /**
     * Get the rate limiter instance.
     *
     * @return \Illuminate\Cache\RateLimiter
     */
    protected function limiter()
    {
        return app(RateLimiter::class);
    }

    /**
     * Get the number of minutes to throttle for.
     *
     * @return int
     */
    public function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 60 * 60;
    }
}
