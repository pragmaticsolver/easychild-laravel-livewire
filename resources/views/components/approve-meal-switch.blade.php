@props([
    'type' => 'button',
    'defaultValue' => false,
    'isDisabled' => false,
    'extraClass' => 'justify-between',
    'colorClass' => '',
    'notEditable' => false,
])

@php
    $newClasses = '';

    if ($defaultValue) {
        $newClasses .= 'bg-green-400 ';
    } else {
        $newClasses .= 'bg-red-500 ';
    }

    if ($isDisabled) {
        $newClasses .= 'opacity-50 ';
    }

    if ($notEditable || $isDisabled) {
        $newClasses .= 'cursor-default ';
    }

    if (! $notEditable && ! $isDisabled) {
        $newClasses .= 'cursor-pointer ';
    }
@endphp

<button
    {{ $attributes->merge(['class' => 'inline-flex items-center border-transparent rounded-full p-2 focus:outline-none text-white ' . $extraClass . ' ' . $colorClass . ' ' . $newClasses ]) }}
    type="{{ $type }}"
>
    {{ $slot }}
</button>
