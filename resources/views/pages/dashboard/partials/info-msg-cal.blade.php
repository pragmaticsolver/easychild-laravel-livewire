@php
    $width = 'lg:w-1/3';

    if (auth()->user()->isPrincipal()) {
        $width = 'xl:w-1/3';
    }
@endphp

{{-- Informations --}}
@include('pages.dashboard.partials.informations')

{{-- messages --}}
@include('pages.dashboard.partials.messages')

{{-- calendar --}}
@include('pages.dashboard.partials.calendar')
