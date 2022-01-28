@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="$user->organization->name"></x-h1title>
@endsection

@section('full-content')
    <div class="flex flex-wrap items-start -mx-2">

        @include('pages.dashboard.partials.pie-charts')

        @include('pages.dashboard.partials.info-msg-cal')

        {{-- Organization detail --}}
        <div class="mb-8 px-2 w-full md:w-1/2 lg:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('dashboard.your_org') }}
            </h2>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="px-4 py-5 sm:p-6 flex items-center" style="min-height: 212px;">
                    <div class="w-full flex items-center">
                        <dl class="flex-grow pr-4">
                            <dt class="text-xl leading-8 font-semibold text-gray-900">
                                {{ $organization->name }}
                            </dt>

                            @php
                                $phoneNo = null;

                                $orgSettings = $organization->settings;

                                if (Arr::has($orgSettings, 'phone')) {
                                    $phoneNo = $orgSettings['phone'];
                                }
                            @endphp

                            @if ($phoneNo)
                                <dd class="text-base leading-5 font-medium text-gray-500 mt-2">
                                    <a href="tel:{{ $phoneNo }}" class="inline-flex items-center">
                                        <x-heroicon-o-phone class="w-5 h-5 mr-3" />

                                        {{ trans('organizations.phone') }} <span class="text-sm">{{ $phoneNo }}</span>
                                    </a>
                                </dd>
                            @endif

                            <dt class="mt-2 text-lg font-semibold text-gray-900">
                                {{ now()->format(config('setting.format.date')) }} | {{ now()->dayName }}
                            </dt>

                            <dd class="text-base leading-5 font-medium text-gray-500 truncate pb-0.5">
                                @if ($openingTime)
                                    {!! trans('dashboard.opening_times_detail', ['from' => $openingTime['start'], 'to' => $openingTime['end']]) !!}
                                @else
                                    {{ trans('dashboard.opening_times_not_available') }}
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <div
                        class="flex-shrink-0"
                        x-data="window.userAvatarFunc('{{ auth()->user()->organization_avatar }}', 'org-avatar')"
                        x-init="onInit()"
                    >
                        <img width="90" :src="visibleAvatar" alt="{{ auth()->user()->organization ? auth()->user()->organization->name : 'Org Avatar' }}">
                    </div>
                </div>

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="{{ route('organizations.profile') }}"
                            class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.org_profile_link') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- opening times --}}
        {{-- <div class="mb-8 px-2 w-full md:w-1/2 lg:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('dashboard.opening_times_title') }}
            </h2>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="px-4 py-5 sm:p-6 flex items-center" style="min-height: 212px;">
                    <dl class="w-full">
                        <dt class="text-xl leading-8 font-semibold text-gray-900">
                            {{ now()->format(config('setting.format.date')) }} | {{ now()->dayName }}
                        </dt>
                        @if ($openingTime)
                            <dd class="text-base leading-5 font-medium text-gray-500 truncate pb-0.5">
                                {!! trans('dashboard.opening_times_detail', ['from' => $openingTime['start'], 'to' => $openingTime['end']]) !!}
                            </dd>
                        @else
                            <dd class="text-base leading-5 font-medium text-gray-500 truncate pb-0.5">
                                {{ trans('dashboard.opening_times_not_available') }}
                            </dd>
                        @endif
                    </dl>
                </div>

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="{{ route('openingtimes.index') }}"
                            class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.org_schedules_setting_link') }}
                        </a>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- users list --}}
        @include('pages.dashboard.partials.userslist')
    </div>
@endsection
