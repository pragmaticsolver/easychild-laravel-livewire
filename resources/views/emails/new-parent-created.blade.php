@extends('layouts.email')

@section('content')
    <x-emails.h1>
        @if ($user->given_names)
            {{ trans('mail.greeting', ['name' => $user->given_names]) }}
        @else
            {{ trans('users.parent.new-email.greetings', ['org' => $organization->name]) }}
        @endif
    </x-emails.h1>

    @if (! $onlyChildLinked)
        <x-emails.panel>{{ trans('mail.user_created_line_1') }}</x-emails.panel>
    @endif

    <x-emails.panel>{{ trans('users.parent.new-email.child_linked_para', ['name' => $child->given_names]) }}</x-emails.panel>

    <x-emails.panel>{{ trans('mail.user_created_line_2') }}</x-emails.panel>

    @if ($onlyChildLinked)
        <x-emails.button :link="$loginRoute" :text="trans('users.parent.new-email.login_link_text_new_linked')"></x-emails.button>
    @else
        <x-emails.button :link="$loginRoute" :text="trans('users.parent.new-email.login_link_text_new_account')"></x-emails.button>
    @endif

    <x-emails.panel>{{ trans('mail.user_created_line_3') }}</x-emails.panel>

    <div style="width: 200px; height: 200px; margin: 0 auto 20px;">
        <img width="200" height="200" src="{{ $message->embed($qrCode) }}" alt="qr code">
        {{-- <img width="200" height="200" src="{{ $qrCode }}" alt="qr code"> --}}
    </div>
@endsection
