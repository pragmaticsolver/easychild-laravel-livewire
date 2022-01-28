<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\HasLoginThrottle;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    use HasLoginThrottle;

    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        if ($this->checkForTooManyLoginAttempts()) {
            return $this->sendIpBannedView();
        }

        if ($token = $this->checkQuery(request(), 'token')) {
            return $this->checkTokenBasedLogin($token);
        }

        return $this->sendLoginView();
    }

    public function destroy(Request $request)
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session()->flash('success', trans('auth.logout'));
        session()->flash('logout', 'logout');

        return redirect(route('home'));
    }
}
