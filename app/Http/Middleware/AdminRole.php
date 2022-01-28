<?php

namespace App\Http\Middleware;

use Closure;

class AdminRole
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
        if ($request->user()->role !== 'Admin') {
            session()->flash('error', trans('extras.admin_only'));

            return redirect(route('dashboard'));
        }

        return $next($request);
    }
}
