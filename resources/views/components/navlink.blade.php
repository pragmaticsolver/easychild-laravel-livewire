@props([
    'activeClass' => 'bg-gray-900',
    'hoverClass' => 'hover:bg-gray-700',
    'textClass' => 'text-white',
    'href' => '#',
    'hasActive' => false,
    'active' => false,
])

@php
    $isActive = Str::startsWith(request()->url(), $href);

    if ($hasActive && $active) {
        $isActive = true;
    }
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'px-3 py-2 rounded-md lg:text-sm font-medium focus:outline-none ' . $textClass . ' ' . ($isActive ? $activeClass : $hoverClass)]) }}
>
    {{ $slot }}
</a>
