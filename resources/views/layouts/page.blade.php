<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body
        x-data
        x-cloak
        x-on:reload-browser.window="location.reload()"
        class="bg-gray-100 custom-scrollbar overflow-x-hidden bg-white-logo min-h-screen"
    >
        @include('partials.flash')

        @include('partials.header')

        @yield('pageTitle')

        @hasSection('content')
            <div class="max-w-7xl mx-auto py-6 px-4">
                @yield('content')
            </div>
        @endif

        @hasSection('full-content')
            <div class="py-6 px-4">
                @yield('full-content')
            </div>
        @endif

        @hasSection('full-content-no-padding')
            @yield('full-content-no-padding')
        @endif

        @if (auth()->check() && auth()->user()->isPrincipal())
            @livewire('components.switch-principal-group')
        @endif

        @livewire('components.offline')

        @include('partials.extra-with-impersonation')

        @stack('scripts')
    </body>
</html>
