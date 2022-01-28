@extends('layouts.page')

@section('pageTitle')
    <x-h1title
        :page-title="trans('dashboard.title')"
        :show-child-switcher="auth()->user()->isParent()"
    />
@endsection

@section('content')
    <div class="flex flex-wrap items-start -mx-2">
        @livewire('components.user-org-block')
    </div>
@endsection
