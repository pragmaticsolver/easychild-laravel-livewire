<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function request()
    {
        $title = trans('auth.password_reset_title');

        return view('pages.guest', [
            'livewire' => 'auth.password-request',
            'title' => $title,
            'showAuthHeader' => true,
        ]);
    }

    public function reset(Request $request, $token)
    {
        $title = trans('auth.password_reset_title');
        $email = $request->get('email');

        return view('pages.guest', [
            'livewire' => 'auth.password-reset',
            'title' => $title,
            'showAuthHeader' => true,
            'data' => compact('token', 'email'),
        ]);
    }
}
