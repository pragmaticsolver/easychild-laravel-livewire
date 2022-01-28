<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body
        x-data
        x-cloak
        class="bg-gray-100 custom-scrollbar overflow-x-hidden bg-white-logo min-h-screen"
    >
        @include('partials.flash')

        <div class="h-screen flex flex-col overflow-hidden">
            <div class="top-header-area">
                @include('partials.header')

                @yield('pageTitle')
            </div>

            @hasSection('content')
                @yield('content')
            @endif
        </div>

        @if (auth()->check() && auth()->user()->isPrincipal())
            @livewire('components.switch-principal-group')
        @endif

        @livewire('components.offline')

        @include('partials.extra-with-impersonation')

        @stack('scripts')
    </body>
</html>
