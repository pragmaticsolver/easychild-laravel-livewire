<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;

class CheckForParent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isParent()) {
            if (! $user->given_names || ! $user->last_name) {
                if (! $request->routeIs('parent.signup')) {
                    $url = URL::signedRoute('parent.signup', [
                        'token' => $user->token,
                    ]);

                    return redirect($url);
                }
            }

            if (! $user->parent_current_child) {
                // No user Assigned
                auth()->logout();

                return redirect()->route('login');
            }
        }

        if ($user && $user->isUser()) {
            auth()->logout();

            session()->flash('logout', 'logout');

            return redirect()->route('login');
        }

        return $next($request);
    }
}
