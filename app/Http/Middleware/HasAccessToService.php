<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasAccessToService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $service)
    {
        $user = $request->user();

        $canAccess = $user->hasAccessToService($service);

        if (! $canAccess) {
            session()->flash('error', trans('extras.admin_and_manager_only'));

            return redirect(route('dashboard'));
        }

        return $next($request);
    }
}
