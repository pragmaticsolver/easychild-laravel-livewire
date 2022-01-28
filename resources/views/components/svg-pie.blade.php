@props([
    'pieData' => [],
    'eventName' => 'time-schedules',
    'totalText',
    'mealPlan',
    'currentText',
    'background' => 'img/dashboard-img-02.svg',
])

@php
    $xData = [
        'pieData' => [
            'total' => $pieData['total'],
            'currentValue' => $pieData['currentValue'],
            'percent' => $pieData['percent'],
        ],
        'eventName' => "principal-dashboard-{$eventName}",
    ];
@endphp

<div
    class="relative"
    x-data="{{ json_encode($xData) }}"
    x-on:principal-dashboard-{{ $eventName }}.window="pieData = $event.detail"
>
    <div class="absolute right-0 bottom-0">
        <img src="{{ asset($background) }}" alt="">
    </div>

    <svg
        {{ $attributes->merge(['class' => 'relative mx-auto -mb-17' ]) }}
        style="width: 210px;height: auto;"
        viewBox="0 0 40 40"
    >
        <circle cx="20" cy="20" fill="transparent" r="16" />
        <circle
            class="shadow-lg"
            x-bind:class="{
                'text-pie-red-bg': eventName == 'principal-dashboard-time-schedules',
                'text-pie-yellow-bg': eventName == 'principal-dashboard-prescense-schedules',
                'text-pie-orange-bg': eventName.indexOf('principal-dashboard-meal-plan') == 0,
            }"
            cx="20" cy="20" fill="transparent" r="16" stroke="currentColor" stroke-dasharray="100 0" stroke-dashoffset="70" stroke-width="4.5"
        />
        <circle
            x-bind:class="{
                'text-pie-red-fg': eventName == 'principal-dashboard-time-schedules',
                'text-pie-yellow-fg': eventName == 'principal-dashboard-prescense-schedules',
                'text-pie-orange-fg': eventName.indexOf('principal-dashboard-meal-plan') == 0,
            }"
            class="origin-center transform rotate-90" cx="20" cy="20" fill="transparent" r="16" stroke-linecap="round" stroke="currentColor" x-bind:stroke-dasharray="pieData.percent" stroke-dashoffset="100" stroke-width="4.5"
        />

        @if(Str::startsWith($eventName, 'meal-plan'))
            <text class="text-xs text-pie-text" text-anchor="middle" dominant-baseline="central" fill="currentColor" x="50%" y="50%" x-html="pieData.total"></text>
        @else
            <text class="text-xs text-pie-text" text-anchor="middle" dominant-baseline="central" fill="currentColor" x="50%" y="50%" x-html="pieData.currentValue"></text>
        @endif
    </svg>

    <div class="text-right p-4 relative flex items-end justify-between">
        @isset($mealPlan)
            <div class="text-left">
                <span>{{ trans('schedules.' . $mealPlan) }}</span> <br>
            </div>
        @endif

        <div class="text-right">
            <div class="inline-block text-left text-sm">
                {{ $totalText }}: <span x-text="pieData.total"></span> <br>
                {{ $currentText }}: <span x-text="pieData.currentValue"></span>
            </div>
        </div>
    </div>
</div>
