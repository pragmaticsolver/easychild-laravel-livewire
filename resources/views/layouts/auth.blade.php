@extends('layouts.base')

@section('body')
    <div class="flex flex-col justify-center min-h-screen py-12 bg-gray-100 sm:px-6 lg:px-8 bg-gradient">
        @yield('content')
    </div>
@endsection
