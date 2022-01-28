@php
    $userLogged = auth()->check();
    $impersonating = false;

    if ($userLogged) {
        $impersonating = auth()->user()->isImpersonated();
    }
@endphp

@livewire('components.impersonator')

@if (! request()->routeIs('parent.signup'))
    @if (!$userLogged || ! $impersonating)
        <div>
            @livewire('components.pwa-login-manager')

            @include('components.service-worker-app-install')
        </div>
    @endif
@endif
