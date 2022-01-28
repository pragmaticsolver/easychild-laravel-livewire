<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class UserLanguageModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = config('app.locale');

        if ($user = $request->user()) {
            $settings = $user->settings;

            if (Arr::has($settings, 'lang')) {
                $locale = $settings['lang'];
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
