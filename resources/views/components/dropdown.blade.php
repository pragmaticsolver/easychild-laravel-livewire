@props([
    'notAbsolute' => false,
    'isMobileNav' => false,
    'dropLeft' => true,
])

@php
    $holderClass = 'origin-top-right z-10';

    if ($dropLeft) {
        $holderClass .= ' right-0';
    }

    if ($isMobileNav) {
        $holderClass .= ' ';

        if (! $notAbsolute) {
            $holderClass .= ' md:absolute md:w-48 md:mt-2 md:rounded-md';
        }
    } else {
        if (! $notAbsolute) {
            $holderClass .= ' absolute w-48 mt-2 rounded-md';
        }
    }
@endphp

<div
    {{ $attributes->merge(['class' => 'relative']) }}
    x-data="{dropOpen: false}"
    x-on:click.away="dropOpen = false"
>
    {{ $button }}

    <div
        x-show="dropOpen"
        class="{{ $holderClass }}"
        x-transition:enter="transition ease-in duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-out duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
