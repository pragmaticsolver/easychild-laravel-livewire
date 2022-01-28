@php
    $width = 'lg:w-1/3';

    if (auth()->user()->isPrincipal()) {
        $width = 'xl:w-1/3';
    }
@endphp

<div class="mb-8 px-2 w-full md:w-1/2 {{ $width }}">
    <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
        {{ trans('dashboard.calendar_title') }}
    </h2>

    <x-date-calendar></x-date-calendar>
</div>
