@props([
    'type' => 'button',
    'defaultValue',
    'options' => [true, false],
    'backgrounds' => ['bg-green-600', 'bg-red-500'],
    'dim' => false,
    'notEditable' => false,
])

@php
    $switch = [
        'state' => $defaultValue,
    ];
@endphp

<button
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-between border-transparent rounded-full p-2 focus:outline-none text-white' ]) }}
    x-data="{
        state: @json($defaultValue),
        options: @json($options),
        dim: @json($dim),
        notEditable: @json($notEditable),
        stateChange() {
            if (this.notEditable) return;

            var current = this.options.indexOf(this.state);
            current++;

            if (this.options.length == current) {
                current = 0;
            }

            this.state = this.options[current];
        }
    }"
    type="{{ $type }}"
    x-on:click.prevent="stateChange"
    x-init="$watch('state', value => $dispatch('input', value))"
    x-bind:disabled="notEditable"
    :class="{
        '{{ $backgrounds[0] }}': options[0] == state,
        '{{ $backgrounds[1] }}': options[1] == state,
        'opacity-50': dim,
        'cursor-default': notEditable,
    }"
>
    {{ $slot }}
</button>
