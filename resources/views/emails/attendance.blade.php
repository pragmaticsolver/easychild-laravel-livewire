@extends('layouts.email')

@section('content')
    <x-emails.h1>{{ trans('mail.greeting', ['name' => $user->given_names]) }}</x-emails.h1>

    <x-emails.panel>{{ trans('schedules.notification.title', ['name' => $schedule->user->given_names]) }}</x-emails.panel>

    <x-emails.two-buttons
        link1="#"
        :text1="trans('schedules.notification.approve_text')"
        link2="#"
        :text2="trans('schedules.notification.reject_text')"
    ></x-emails.two-buttons>
@endsection
