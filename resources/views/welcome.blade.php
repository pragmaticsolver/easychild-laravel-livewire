@extends('layouts.app')

@section('content')
    <div x-data="clearSavedImages()" x-init="onInit()" class="flex flex-col justify-center min-h-screen py-12 bg-gray-100 sm:px-6 lg:px-8 bg-gradient">
        <div class="flex items-center justify-center">
            <div class="flex flex-col justify-around">
                <div class="text-center">
                    <a href="{{ route('home') }}">
                        <x-logo-image :white="true" class="w-auto h-24 mx-auto text-indigo-600" />
                    </a>

                    <div class="mb-8">
                        <h1 class="mt-0 mb-4 text-white font-light leading-none tracking-wider text-center font-quicksand text-5xl sm:text-6xl">
                            {{ config('app.name') }}
                        </h1>
                        <p class="m-0 font-quicksand tracking-wider font-light text-sm sm:text-base md:text-lg font-serif text-white">Kindergarten-Manager</p>
                    </div>

                    <div>
                        @auth
                            <a href="{{ route('dashboard') }}" class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none transition duration-150 ease-in-out">{{ trans('nav.dashboard') }}</a>

                            {{-- <a
                                href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none transition duration-150 ease-in-out"
                            >
                            {{ trans('nav.sign_out') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form> --}}
                        @else
                            <a href="{{ route('login') }}" class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none transition duration-150 ease-in-out">{{ trans('nav.sign_in') }}</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
