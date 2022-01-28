<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ParentLink;
use App\Models\User;

class ParentSignUpController extends Controller
{
    public function __invoke($token)
    {
        $parentLink = ParentLink::query()
            ->where('token', $token)
            ->first();

        auth()->logout();

        if (! $parentLink) {
            return $this->checkForParentSignInToken($token);
        }

        $parent = User::query()
            ->where('role', 'Parent')
            ->where('email', $parentLink->email)
            ->first();

        if (! $parent) {
            // Sorry parent not found
            session()->flash('error', trans('users.parent.signup_account_not_setup'));

            return redirect()->route('login');
        }

        if (
            ($parentLink && $parentLink->linked)
            || ($parent->given_names && $parent->last_name)
        ) {
            if (! $parentLink->linked) {
                $parentLink->update([
                    'linked' => true,
                ]);
            }

            return $this->sendToDashboard($parent);
        }

        return $this->sendToSignupPage($token);
    }

    private function checkForParentSignInToken($token)
    {
        $parent = User::query()
            ->where('role', 'Parent')
            ->where('token', $token)
            ->first();

        if ($parent) {
            if (! $parent->given_names || ! $parent->last_name) {
                return $this->sendToSignupPage($token);
            }

            return $this->sendToDashboard($parent);
        }

        // sorry you have invalid token
        session()->flash('error', trans('users.parent.signup_invalid_token'));

        return redirect()->route('login');
    }

    private function sendToDashboard($parent)
    {
        auth()->login($parent);

        session()->flash('success', trans('auth.welcome_signed_up_already'));

        return redirect()->route('dashboard');
    }

    private function sendToSignupPage($token)
    {
        return view('pages.guest', [
            'livewire' => 'auth.parent-signup',
            'title' => trans('users.parent.signup_title'),
            'showAuthHeader' => true,
            'data' => [
                'token' => $token,
            ],
        ]);
    }
}
