@extends('layouts.app')

@section('content')
    <div class="md:flex min-h-screen">
        <div class="w-full bg-white flex items-center justify-center">
            <div class="max-w-sm m-8">
                <div class="text-black text-3xl md:text-5xl mb-5 font-black">
                    @yield('code', trans('errors.offline_title'))
                </div>

                <p class="text-grey-darker text-xl md:text-2xl font-light mb-8 leading-normal">
                    @yield('message', trans('errors.offline_message'))
                </p>

                <a href="{{ app('router')->has('home') ? route('home') : url('/') }}">
                    <button class="bg-transparent text-grey-darkest font-bold uppercase tracking-wide py-3 px-6 border-2 border-grey-light hover:border-grey rounded-lg">
                        {{ trans('errors.offline_refresh') }}
                    </button>
                </a>
            </div>
        </div>
    </div>
@endsection
