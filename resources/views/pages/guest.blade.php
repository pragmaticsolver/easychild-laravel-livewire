@extends('layouts.auth')
@section('title', $title)

@section('content')
    @if (isset($showAuthHeader) && $showAuthHeader)
        <x-auth-headers :title="$title"></x-auth-headers>
    @endif

    @livewire($livewire, isset($data) ? $data : [])
@endsection
