<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>

    <body x-cloak x-data class="bg-gray-100 bg-white-logo overflow-x-hidden custom-scrollbar">
        @include('partials.flash')

        @yield('body')

        @if (auth()->check() && auth()->user()->isPrincipal())
            @livewire('components.switch-principal-group')
        @endif

        @livewire('components.offline')

        @include('partials.extra-with-impersonation')

        @stack('scripts')
    </body>
</html>
