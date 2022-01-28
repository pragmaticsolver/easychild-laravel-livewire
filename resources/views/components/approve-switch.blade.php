@props([
    'type' => 'button',
    'defaultValue' => false,
    'disabled' => false,
    'options' => null,
])

@php
    $switch = [
        'state' => $defaultValue,
        'disabled' => $disabled,
        'options' => $options
    ];
@endphp

<button
    x-data="{{ json_encode($switch) }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium leading-4 text-white outline-none focus:outline-none' ]) }}
    {{ $disabled ? ' disabled' : ''}}
    :class="{
        'bg-green-500': state === 'approved',
        'bg-red-500': state === 'declined',
        'bg-yellow-400': state === 'pending',
        'cursor-pointer': !disabled,
        'cursor-default': disabled
    }"
    x-on:click.prevent="
        if (state === 'approved') { return state = 'declined'; };
        if (state === 'pending') { return state = 'approved'; };
        if (state === 'declined') { return state = 'approved'; };
    "
    x-init="$watch('state', value => $dispatch('input', value))"
    type="{{ $type }}"
    x-text="options[state]"
></button>
