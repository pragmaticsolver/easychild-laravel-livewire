<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIfHasGroupAssigned
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
        $user = $request->user();

        if ($user && ($user->isPrincipal() || $user->isParent())) {
            $groupsCount = 0;

            if ($user->isParent()) {
                $currentChild = $user->parent_current_child;

                $groupsCount = optional(optional($currentChild)->groups())->count();
            } else {
                $groupsCount = $user->groups()->count();
            }

            if (! $groupsCount) {
                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();

                session()->flash('error', trans('errors.no_group_assigned'));

                return redirect(route('login'));
            }
        }

        return $next($request);
    }
}
