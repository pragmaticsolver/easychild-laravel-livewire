@props([
    'start' => '00:00',
    'end' => '23:30',
    'label' => 'Select a time',
    'error' => '',
    'minInterval' => 15,
    'wireKey' => 'time-picker',
])

@php
    $selectTimes = getTimePickerValues($start, $end, $minInterval);
@endphp

<select
    {{ $attributes->merge(['class' => $error . ' ' ]) }}
    wire:key="{{ $wireKey }}"
>
    <option value="">{{ $label }}</option>
    @foreach($selectTimes as $selectTime)
        <option value="{{ $selectTime['value'] }}">{{ $selectTime['text'] }}</option>
    @endforeach
</select>
{{-- <x-tailwind-select
    :select-items="$selectTimes"
    current-value="06:00:00"
/> --}}
