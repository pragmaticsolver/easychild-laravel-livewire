<?php

namespace App\Actions\User;

use App\Models\User;
use App\Notifications\NewPrincipalCreatedNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

class NewPrincipalCreatedAction
{
    use AsObject;

    public function handle(User $user, $data = [])
    {
        if ($user->role != 'Principal') {
            return;
        }

        $isPasswordReset = false;

        if ($data && count($data) && Arr::has($data, 'password-reset') && $data['password-reset']) {
            $isPasswordReset = true;
        }

        $managers = User::query()
            ->where('role', 'Manager')
            ->where('organization_id', $user->organization_id)
            ->get();

        if ($managers->count()) {
            $password = Str::random(config('setting.auth.passwordLength'));

            $token = $user->token;
            if (! $token) {
                $token = Str::random(config('setting.auth.tokenLength'));
            }

            $user->update([
                'password' => $password,
                'token' => $token,
            ]);

            Notification::send($managers, new NewPrincipalCreatedNotification($user, $password, $isPasswordReset));
        }
    }
}
