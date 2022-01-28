@props([
    'timeEnabled' => false,
])

@php
    $visibleFormat = config('setting.format.javascript.date');
    $dbFormat = 'Y-m-d';
    $locale = config('app.locale');
@endphp

<div wire:ignore>
    <input
        x-data="singleFlatPicker('{{ $visibleFormat }}', '{{ $dbFormat }}', '{{ $locale }}', {
            value: @entangle($attributes->wire('model')),
            instance: undefined,
            @if ($attributes->has('wire:notenabletime'))
                notEnableTime: @entangle($attributes->wire('notenabletime')),
            @else
                notEnableTime: ! (Boolean('{{ $timeEnabled }}') || false),
            @endif
        })"
        x-on:date-picker-clear.window="instance.setDate(null, false)"
        x-init="init($refs.input, $watch)"
        x-ref="input"
        x-bind:value="value"
        type="text"
        autocomplete="off"
        {{ $attributes->merge(['class' => 'text-field']) }}
    >
</div>
