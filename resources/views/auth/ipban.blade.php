@extends('layouts.app')

@section('content')
    <div class="flex flex-col justify-center min-h-screen py-12 bg-gray-100 sm:px-6 lg:px-8">
        <div class="flex items-center justify-center">
            <div class="flex flex-col justify-around">
                <div class="space-y-6">
                    <a href="{{ route('home') }}">
                        <x-logo-image class="w-auto h-16 mx-auto text-indigo-600" />
                    </a>

                    <h1 class="text-5xl font-extrabold tracking-wider text-center text-gray-600">
                        {{ config('app.name') }}
                    </h1>

                    <h2 class="text-3xl mb-3 font-extrabold tracking-wider text-center text-gray-600">
                        {{ trans('auth.ip_banned') }}
                    </h2>

                    @if(session()->has('message'))
                        <p>
                            {{ session('message') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
