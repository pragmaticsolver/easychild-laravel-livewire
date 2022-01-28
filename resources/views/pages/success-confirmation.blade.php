@extends('layouts.auth')
@section('title', $title)

@section('content')
    <div class="max-w-sm mx-auto">
        <x-ask-confirmation-header class="mb-4" :title="$title"></x-ask-confirmation-header>

        <div class="text-center text-base text-white mb-4">
            {{ $message }}
        </div>

        <div class="text-center">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
            >
                {{ trans('extras.go_to_dashboard') }}
            </a>
        </div>
    </div>
@endsection
