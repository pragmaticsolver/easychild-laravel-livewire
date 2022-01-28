@php
    $isLogout = false;
    $user = auth()->user();

    if (session()->has('logout') && session('logout') === 'logout') {
        $isLogout = true;
    }

    $isProfileRoute = false;
    $isPushEnabled = false;
    $currentEndPoints = collect();

    if (request()->routeIs('users.profile')) {
        $isProfileRoute = true;
    }

    if ($user) {
        $currentEndPoints = $user->pushSubscriptions->pluck('endpoint');

        if ($user->settings) {
            if (Arr::has($user->settings, 'push') && $user->settings['push']) {
                $isPushEnabled = true;
            }
        }
    }

    $vapIdPublic = config('webpush.vapid.public_key');

    $notificationMessages = collect([
        'isProfileRoute' => $isProfileRoute,
        'isPushEnabled' => $isPushEnabled,
        'not-supported' => trans('users.notification.not-supported'),
        'unable-to-sub-push' => trans('users.notification.unable-to-sub-push'),
        'disabled' => trans('users.notification.disabled'),
        'permission-denied' => trans('users.notification.permission-denied'),
        'push-not-supported' => trans('users.notification.push-not-supported'),
        'push-confirm' => [
            'title' => trans('users.push-confirm.title'),
            'description' => $isProfileRoute ? trans('users.push-confirm.description_on_profile_page') : trans('users.push-confirm.description'),
            'confirm-text' => trans('users.push-confirm.confirm-text'),
            'cancel-text' => trans('users.push-confirm.cancel-text'),
        ],
    ]);
@endphp

<div>
    <div
        x-data="pwaLoginManager(@json($isLogout), $wire, '{{ $vapIdPublic }}')"
        x-init="onMounted(@json(auth()->check()), {{ $notificationMessages }}, {{ $currentEndPoints }}, $dispatch)"
        @auth
            x-on:confirmed-route-to-profile.window="onConfirmedRouteToProfile(@this)"
            x-on:canceled-route-to-profile.window="setLaterDateForPushEnabledCheck()"
            x-on:confirm-user-logout.window="confirmUserLogout($event.detail, @json(auth()->user()->isParent()), $dispatch)"
            x-on:logout-user.window="afterUserConfirmLogout()"
            x-on:user-push-notification-toggle.window="serviceWorkerToggle($event.detail)"
        @endauth
    >
        <x-confirm-modal confirm-id="pwa-login-manager"></x-confirm-modal>
    </div>

    <x-confirm-modal confirm-id="check-push-enabled"></x-confirm-modal>
</div>
