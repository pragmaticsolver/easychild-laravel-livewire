@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="$user->organization->name"></x-h1title>
@endsection

@section('content')
    @livewire('group-class.index', [])
@endsection
