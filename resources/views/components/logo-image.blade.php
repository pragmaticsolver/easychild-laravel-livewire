@props([
    'white' => false,
])

@if ($white)
    <img {{ $attributes }} src="{{ asset('img/easychild-white.svg') }}" alt="{{ config('app.name') }}">
@else
    <img {{ $attributes }} src="{{ asset('img/easychild.svg') }}" alt="{{ config('app.name') }}">
@endif

