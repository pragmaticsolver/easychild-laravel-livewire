@extends('layouts.email')

@section('content')
    <x-emails.h1>{{ trans('mail.greeting', ['name' => $user->given_names]) }}</x-emails.h1>

    @if (isset($isPasswordReset) && $isPasswordReset)
        <x-emails.panel>{{ trans('mail.reset_password.line_1') }}</x-emails.panel>
    @else
        <x-emails.panel>{{ trans('mail.user_created_line_1') }}</x-emails.panel>
    @endif

    <x-emails.table>
        <x-slot name="headers">
            <x-emails.table.header></x-emails.table.header>
            <x-emails.table.header></x-emails.table.header>
        </x-slot>

        <x-slot name="rows">
            <x-emails.table.rows>
                <x-emails.table.row>
                    {{ trans('users.given_name') }}
                </x-emails.table.row>
                <x-emails.table.row>
                    {{ $user->given_names }}
                </x-emails.table.row>
            </x-emails.table.rows>

            <x-emails.table.rows>
                <x-emails.table.row>
                    {{ trans('users.last_name') }}
                </x-emails.table.row>
                <x-emails.table.row>
                    {{ $user->last_name }}
                </x-emails.table.row>
            </x-emails.table.rows>

            <x-emails.table.rows>
                <x-emails.table.row>
                    {{ trans('users.email') }}
                </x-emails.table.row>
                <x-emails.table.row>
                    {{ $user->email }}
                </x-emails.table.row>
            </x-emails.table.rows>

            <x-emails.table.rows>
                <x-emails.table.row>
                    {{ trans('users.password') }}
                </x-emails.table.row>
                <x-emails.table.row>
                    {{ $password }}
                </x-emails.table.row>
            </x-emails.table.rows>

        </x-slot>
    </x-emails.table>

    @if(in_array($user->role, ['User', 'Parent']))
        <x-emails.panel>{{ trans('mail.user_created_line_2') }}</x-emails.panel>

        <x-emails.button :link="$loginRoute" :text="trans('mail.user_created_btn_text')"></x-emails.button>

        <x-emails.panel>{{ trans('mail.user_created_line_3') }}</x-emails.panel>

        <div style="width: 200px; height: 200px; margin: 0 auto 20px;">
            <img width="200" height="200" src="{{ $message->embed($qrCode) }}" alt="qr code">
            {{-- <img width="200" height="200" src="{{ $qrCode }}" alt="qr code"> --}}
        </div>
    @else
        <x-emails.button :link="$loginRoute" :text="trans('mail.user_created_btn_text')"></x-emails.button>
    @endif
@endsection
