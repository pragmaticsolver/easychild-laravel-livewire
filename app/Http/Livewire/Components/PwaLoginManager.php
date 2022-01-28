<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Component;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PwaLoginManager extends Component
{
    public function userAccessToken()
    {
        if (auth()->check()) {
            return auth()->user()->token;
        }

        return null;
    }

    public function routeToProfile()
    {
        return redirect()->route('users.profile');
    }

    public function userLogout($subscription)
    {
        if ($subscription && Arr::has($subscription, 'endpoint')) {
            auth()->user()->deletePushSubscription($subscription['endpoint']);

            // DB::table('push_subscriptions')
            //     ->where('subscribable_id', auth()->id())
            //     ->delete();
        }

        auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        session()->flash('success', trans('auth.logout'));
        session()->flash('logout', 'logout');

        return redirect(route('home'));
    }

    public function updateSubscription($data)
    {
        auth()->user()->updatePushSubscription(
            $data['endpoint'],
            $data['publicKey'],
            $data['authToken'],
            $data['contentEncoding'],
        );
    }

    public function removeSubscription($data = null)
    {
        if ($data) {
            auth()->user()->deletePushSubscription($data['endpoint']);
        } else {
            DB::table('push_subscriptions')
                ->where('subscribable_id', auth()->id())
                ->delete();
        }
    }

    public function removeAllSubscription()
    {
        auth()->user()->deletePushSubscription($data['endpoint']);
    }

    public function loginUserUsingToken($token)
    {
        if (auth()->check()) {
            return;
        }

        $user = User::where('token', $token)->first();

        if ($user) {
            auth()->login($user, true);

            session()->flash('success', trans('auth.successful_token_login', ['fullname' => $user->full_name]));

            redirect(route('dashboard'));
        }
    }

    public function render()
    {
        return view('livewire.components.pwa-login-manager');
    }
}
