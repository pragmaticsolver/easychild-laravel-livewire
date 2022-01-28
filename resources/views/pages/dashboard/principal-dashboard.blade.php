@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="$user->organization->name"></x-h1title>
@endsection

@section('full-content')
    <div class="flex flex-wrap lg:flex-no-wrap">
        <div class="flex-grow">
            <div class="flex flex-wrap items-start -mx-2">
                @include('pages.dashboard.partials.pie-charts')

                @include('pages.dashboard.partials.info-msg-cal')
            </div>
        </div>

        <div class="flex-shrink-0 w-full lg:w-72 pt-8 lg:pl-4">
            @livewire('components.principal-time-board')
        </div>
    </div>
@endsection
