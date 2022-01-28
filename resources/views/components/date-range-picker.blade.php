@props([
    'startDate' => null,
    'endDate' => null,
])

@php
    $visibleFormat = config('setting.format.javascript.date');
    $dbFormat = 'Y-m-d';
    $locale = config('app.locale');
@endphp

<div
    {{ $attributes->merge(['class' => 'w-full']) }}
    wire:ignore
    x-data
    x-on:date-range-changed="@this.set('{{ $attributes->wire('sync')->value }}', $event.detail)"
>
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <x-heroicon-s-calendar class="h-5 w-5 text-gray-400" />
    </div>

    <input
        x-data="rangeFlatPicker('{{ $visibleFormat }}', '{{ $dbFormat }}', '{{ $locale }}', '{{ $startDate }}', '{{ $endDate }}', {
            value: null,
            instance: undefined,
        })"
        x-init="init($el, $dispatch)"
        class="form-input block w-full pl-10 text-sm leading-6 focus:outline-none shadow-sm"
        placeholder="Date"
    />

    <div wire:loading.class.remove="opacity-0" class="opacity-0 absolute inset-y-0 right-0 pr-3 leading-none flex items-center pointer-events-none">
        <x-loading></x-loading>
    </div>
</div>
