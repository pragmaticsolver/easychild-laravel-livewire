@extends('layouts.page')

@section('pageTitle')
    <x-h1title :page-title="trans('dashboard.title')"></x-h1title>
@endsection

@section('full-content')
    <div class="flex flex-wrap items-start -mx-2">
        <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('dashboard.time_schedule_title') }}
            </h2>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                @livewire('components.pie-chart', [
                    'total' => 100,
                    'currentValue' => 30,
                    'totalText' => trans('dashboard.child_total'),
                    'currentText' => trans('dashboard.child_present'),
                ])

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.show_schedules') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('dashboard.presence_title') }}
            </h2>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                @livewire('components.pie-chart', [
                    'total' => 100,
                    'currentValue' => 30,
                    'totalText' => trans('dashboard.presence_children_planned'),
                    'currentText' => trans('dashboard.presence_children_present'),
                    'bg' => 'text-pie-yellow-bg',
                    'fg' => 'text-pie-yellow-fg',
                ])

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.show_attendance_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
            <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                {{ trans('dashboard.meal_plan_title') }}
            </h2>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                @livewire('components.pie-chart', [
                    'total' => 100,
                    'currentValue' => 30,
                    'totalText' => trans('dashboard.meal_plan_children_eating'),
                    'currentText' => trans('dashboard.meal_plan_signed_out'),
                    'bg' => 'text-pie-orange-bg',
                    'fg' => 'text-pie-orange-fg',
                ])

                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.show_meal_plan') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Informations --}}
        @if (! auth()->user()->isAdmin())
            <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
                <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                    {{ trans('dashboard.informations_title') }}
                </h2>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <ul class="px-4 py-6 space-y-2">
                        <li class="flex items-start justify-start">
                            <div class="mr-2">
                                <x-pdf-svg class="text-black group-hover:text-gray-700"></x-pdf-svg>
                            </div>

                            <span class="font-bold">Information title</span>
                        </li>
                    </ul>

                    <div class="bg-gray-200 px-4 py-4 sm:px-6">
                        <div class="text-sm leading-5">
                            <a href="{{ route('informations.index') }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                {{ trans('dashboard.informations_link') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
                <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                    {{ trans('dashboard.messages_title') }}
                </h2>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <ul class="px-4 py-6 space-y-2">
                        <li class="flex items-start justify-start">
                            <img class="w-12 h-12 mr-3 rounded-full" src="https://picsum.photos/200" alt="">

                            <div class="flex-grow overflow-hidden">
                                <strong class="block font-bold">Title</strong>
                                <p class="text-sm truncate">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Aliquam, quia harum commodi</p>
                            </div>
                        </li>
                    </ul>

                    <div class="bg-gray-200 px-4 py-4 sm:px-6">
                        <div class="text-sm leading-5">
                            <a href="{{ route('informations.index') }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                {{ trans('dashboard.view_messages_link') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($organization)
            <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
                <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                    {{ trans('dashboard.your_org') }}
                </h2>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-lg leading-8 font-semibold text-gray-900">
                                {{ $organization->name }}
                            </dt>
                            <dd class="text-sm leading-5 font-medium text-gray-500">
                                {!! nl2br($organization->address) !!}
                            </dd>
                        </dl>
                    </div>

                    @if (auth()->user()->isPrincipal())
                        <div class="bg-gray-200 px-4 py-4 sm:px-6">
                            <div class="text-sm leading-5">
                                <a href="{{ route('schedules.type.index', ['organization', $organization->uuid]) }}"
                                    class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                    {{ trans('dashboard.view_schedules') }}
                                </a>
                            </div>
                        </div>
                    @endif

                    @if (auth()->user()->isManager())
                        <div class="bg-gray-200 px-4 py-4 sm:px-6">
                            <div class="text-sm leading-5">
                                <a href="{{ route('organizations.profile') }}"
                                    class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                    {{ trans('dashboard.settings') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if (auth()->user()->isUser())
            <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
                <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                    {{ trans('dashboard.user_schedule_title') }}
                </h2>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            @if ($schedule)
                                <dd class="text-sm leading-5 font-medium text-gray-500 truncate">
                                    {{-- {{ dd($schedule) }} --}}
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
                        </dl>
                    </div>

                    @if (auth()->user()->isUser())
                        <div class="bg-gray-200 px-4 py-4 sm:px-6">
                            <div class="text-sm leading-5">
                                <a href="{{ route('schedules.index') }}"
                                    class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                    {{ trans('dashboard.view_schedules') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if (auth()->user()->isManager())
            <div class="mb-8 px-2 w-full md:w-1/2 xl:w-1/3">
                <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
                    {{ trans('dashboard.opening_times_title') }}
                </h2>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dl>
                            <dt class="text-lg leading-8 font-semibold text-gray-900">
                                {{ now()->format(config('setting.format.date')) }} | {{ now()->dayName }}
                            </dt>
                            @if ($openingTime)
                                <dd class="text-sm leading-5 font-medium text-gray-500 truncate">
                                    {!! trans('dashboard.opening_times_detail', ['from' => $openingTime['start'], 'to' => $openingTime['end']]) !!}
                                </dd>
                            @else
                                <dd class="text-sm leading-5 font-medium text-gray-500 truncate">
                                    {{ trans('dashboard.opening_times_not_available') }}
                                </dd>
                            @endif
                        </dl>
                    </div>

                    <div class="bg-gray-200 px-4 py-4 sm:px-6">
                        <div class="text-sm leading-5">
                            <a href="{{ route('openingtimes.index') }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                {{ trans('dashboard.settings') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (auth()->user()->isAdmin())
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dl>
                        <dt class="text-sm leading-5 font-medium text-gray-500">
                            {{ trans('dashboard.total_org') }}
                        </dt>
                        <dd class="mt-1 text-3xl leading-9 font-semibold text-gray-900">
                            {{ $organizations }}
                        </dd>
                    </dl>
                </div>
                <div class="bg-gray-200 px-4 py-4 sm:px-6">
                    <div class="text-sm leading-5">
                        <a href="{{ route('organizations.index') }}"
                            class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                            {{ trans('dashboard.view_all') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($users)
        <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
            {{ trans('dashboard.users_in_org') }}
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($users as $user)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                @if($user->isAdmin())
                                    <x-heroicon-o-sun class="text-white h-6 w-6" />
                                @elseif($user->isPrincipal())
                                    <x-heroicon-o-moon class="text-white h-6 w-6" />
                                @else
                                    <x-heroicon-o-user class="text-white h-6 w-6" />
                                @endif
                            </div>

                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm leading-5 font-medium text-gray-500 truncate">
                                        @if($user->role === 'Admin')
                                            {{ trans('dashboard.role_admin') }}
                                        @elseif($user->role == 'Manager')
                                            {{ trans('dashboard.role_manager') }}
                                        @elseif($user->role == 'Parent')
                                            {{ trans('dashboard.role_parent') }}
                                        @elseif($user->role == 'Principal')
                                            {{ trans('dashboard.role_principal') }}
                                        @elseif($user->role == 'User')
                                            {{ trans('dashboard.role_user') }}
                                        @elseif($user->role == 'Vendor')
                                            {{ trans('dashboard.role_vendor') }}
                                        @endif
                                    </dt>

                                    <dd class="flex items-baseline">
                                        <div class="text-2xl leading-8 font-semibold text-gray-900">
                                            {{ $user->total }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-200 px-4 py-4 sm:px-6">
                        <div class="text-sm leading-5">
                            <a href="{{ route('users.index', ['role' => $user->role]) }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                                {{ trans('dashboard.view_all') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
