@extends('layouts.auth')
@section('title', $title)

@section('content')
    <div class="max-w-sm mx-auto">
        <x-ask-confirmation-header :title="$title"></x-ask-confirmation-header>

        <p class="mb-4 text-base text-center text-white">{{ $description }}</p>

        <div class="flex items-center justify-center">
            <form action="{{ $approveUrl }}" method="POST" class="px-2">
                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                >
                    {{ trans('schedules.notification.approve_text') }}
                </button>
            </form>

            <form action="{{ $approveUrl }}" method="POST" class="px-2">
                @csrf

                <button
                    type="submit"
                    class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                >
                    {{ trans('schedules.notification.reject_text') }}
                </button>
            </form>
        </div>
    </div>
@endsection
