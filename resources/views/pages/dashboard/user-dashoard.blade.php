@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="trans('dashboard.title')"></x-h1title>
@endsection

@section('content')
    <div class="flex flex-wrap items-start -mx-2">
        {{-- Organization detail --}}
        <div class="mb-8 px-2 w-full md:w-1/2 lg:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('dashboard.your_org') }}
            </h2>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="px-4 py-5 sm:p-6 flex flex-col items-center justify-center" style="min-height: 212px;">
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
                                <dd class="text-base leading-5 font-medium text-gray-500">
                                    <a href="tel:{{ $phoneNo }}" class="inline-flex items-center">
                                        <x-heroicon-o-phone class="w-5 h-5 mr-3" />

                                        {{ trans('organizations.phone') }} <span class="text-sm">{{ $phoneNo }}</span>
                                    </a>
                                </dd>
                            @endif
                        </dl>

                        <div
                            class="flex-shrink-0"
                            x-data="window.userAvatarFunc('{{ auth()->user()->organization_avatar }}', 'org-avatar')"
                            x-init="onInit()"
                        >
                            <img width="90" :src="visibleAvatar" alt="{{ auth()->user()->organization ? auth()->user()->organization->name : 'Org Avatar' }}">
                        </div>
                    </div>

                    <div class="w-full">
                        @if ($schedule)
                            <dd class="text-sm leading-5 font-medium text-gray-500 truncate">
                                @if ($schedule->available)
                                    @if ($schedule->status == 'approved' && $schedule->start && $schedule->end)
                                        {{ trans('dashboard.user_schedule', ['from' => Str::of($schedule->start)->replaceLast(':00', ''), 'to' => Str::of($schedule->end)->replaceLast(':00', '')]) }}
                                    @else
                                        {{ trans('dashboard.user_schedule_available') }}
                                    @endif
                                @else
                                    {{ trans('dashboard.user_schedule_not_available') }}
                                @endif
                            </dd>
                        @else
                            <dd class="text-sm leading-5 font-medium text-gray-500 truncate">
                                {{ trans('dashboard.user_no_schedule') }}
                            </dd>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="{{ route('schedules.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.view_schedules') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informations --}}
        @include('pages.dashboard.partials.informations')

        {{-- messages --}}
        @include('pages.dashboard.partials.messages')

        {{-- calendar --}}
        @include('pages.dashboard.partials.calendar')
    </div>
@endsection
