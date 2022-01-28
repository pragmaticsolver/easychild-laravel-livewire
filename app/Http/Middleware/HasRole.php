<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        $roles = Str::of($roles)->lower()->explode('|')->toArray();
        $userRole = (string) Str::of($request->user()->role)->lower();

        if (! in_array($userRole, $roles)) {
            session()->flash('error', trans('extras.admin_and_manager_only'));

            return redirect(route('dashboard'));
        }

        return $next($request);
    }
}
