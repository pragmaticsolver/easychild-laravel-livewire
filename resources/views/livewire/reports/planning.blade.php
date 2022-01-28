@php
    $datePeriod = Carbon\CarbonPeriod::create($startDate, '1 day', $endDate);

    $userTotalSchedule = [];
    $dateTotalSchedule = [];
@endphp

<div class="p-4">
    @if(count($users))
        <div class="bg-white shadow-md border rounded-md px-2 pt-4">
            <div class="flex">
                <div class="flex-shrink-0 w-96 border-r">
                    <div class="relative w-60 mb-3.5">
                        <x-date-range-picker
                            :start-date="$startDate"
                            :end-date="$endDate"
                            wire:sync="dateRange"
                        />
                    </div>

                    @foreach($users as $user)
                        <div wire-key="report-name-{{ $user->uuid }}" class="px-2 py-2 text-sm flex items-center border-t">
                            <div class="flex-1">
                                <div class="truncate" style="max-width: 144px;">
                                    <a class="text-indigo-500" href="{{ route('users.edit', $user->uuid) }}">
                                        {{ $user->full_name }} ({{ $user->id }})
                                    </a>
                                </div>
                            </div>

                            <div class="flex-shrink-0 w-16 px-4">
                                @if ($user->avatar_url)
                                    <img class="inline-block h-8 w-8 rounded-full" src="{{ $user->avatar_url }}" alt="">
                                @else
                                    <svg class="h-8 w-8 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                @endif
                            </div>

                            <div class="flex-shrink-0 w-28 mr-2 whitespace-no-wrap truncate">
                                @if ($user->group_uuid)
                                    <a class="text-indigo-500" href="{{ route('groups.edit', $user->group_uuid) }}">
                                        {{ $user->group_name }}
                                    </a>
                                @else
                                    {{ $user->group_name }}
                                @endif
                            </div>

                            <div class="flex-shrink-0 w-10 border border-report-mantis rounded-full text-center text-sm text-green-500">
                                {{ numformat($user->contract->time_per_day, 1) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex-1 overflow-x-auto custom-scrollbar pt-14">
                    @foreach($users as $user)
                        <div class="flex" wire:key="report-content-{{ $user->uuid }}">
                            <div class="flex-1 border-t">
                                <div class="flex">
                                    @foreach($datePeriod as $date)
                                        @if($date->isWeekday())
                                            @php
                                                $currentData = $user->schedules->where('date', $date->format('Y-m-d'))->first();
                                                $overtime = 0;

                                                if (! Arr::has($userTotalSchedule, $user->uuid)) {
                                                    $userTotalSchedule[$user->uuid] = [
                                                        'available' => 0,
                                                        'overtime' => 0,
                                                        'breakfast' => 0,
                                                        'lunch' => 0,
                                                        'dinner' => 0,
                                                    ];
                                                }

                                                if (! Arr::has($dateTotalSchedule, $date->format('Y-m-d'))) {
                                                    $dateTotalSchedule[$date->format('Y-m-d')] = [
                                                        'available' => 0,
                                                        'overtime' => 0,
                                                        'breakfast' => 0,
                                                        'lunch' => 0,
                                                        'dinner' => 0,
                                                    ];
                                                }
                                            @endphp

                                            <div class="relative w-32 px-2.5 py-2.5">
                                                @if($loop->parent->first)
                                                    <div class="absolute bottom-full left-0 right-0 text-lg text-center px-2 py-2 font-bold">
                                                        {{ $date->format('d.') }}
                                                    </div>
                                                @endif

                                                @if ($currentData && $currentData->available)
                                                    @php
                                                        $overtime = $user->contract->time_per_day - $currentData->presence_diff;

                                                        $hasBreakfastSelected = $currentData->eats_onsite['breakfast'];
                                                        $hasLunchSelected = $currentData->eats_onsite['lunch'];
                                                        $hasDinnerSelected = $currentData->eats_onsite['dinner'];

                                                        $userTotalSchedule[$user->uuid]['available']++;
                                                        $hasBreakfastSelected && $userTotalSchedule[$user->uuid]['breakfast']++;
                                                        $hasLunchSelected && $userTotalSchedule[$user->uuid]['lunch']++;
                                                        $hasDinnerSelected && $userTotalSchedule[$user->uuid]['dinner']++;

                                                        $dateTotalSchedule[$date->format('Y-m-d')]['available']++;
                                                        $hasBreakfastSelected && $dateTotalSchedule[$date->format('Y-m-d')]['breakfast']++;
                                                        $hasLunchSelected && $dateTotalSchedule[$date->format('Y-m-d')]['lunch']++;
                                                        $hasDinnerSelected && $dateTotalSchedule[$date->format('Y-m-d')]['dinner']++;
                                                    @endphp

                                                    <div class="mx-auto w-20 flex items-center justify-center text-center text-sm leading-none border border-report-mantis rounded-full relative">
                                                        <span class="px-0.5 py-1.5 w-5">
                                                            @if ($hasBreakfastSelected)
                                                                {{ trans('vendors.abbr.breakfast') }}
                                                            @else
                                                                &nbsp;
                                                            @endif
                                                        </span>

                                                        <span class="px-0.5 py-1.5 w-5">
                                                            @if ($hasLunchSelected)
                                                                {{ trans('vendors.abbr.lunch') }}
                                                            @else
                                                                &nbsp;
                                                            @endif
                                                        </span>

                                                        <span class="px-0.5 py-1.5 w-5">
                                                            @if ($hasDinnerSelected)
                                                                {{ trans('vendors.abbr.dinner') }}
                                                            @else
                                                                &nbsp;
                                                            @endif
                                                        </span>

                                                        @if ($overtime != 0 && ($profileCare || ($overtime < 0)))
                                                            <span class="pl-1 bg-white -ml-2 absolute left-full text-center text-xs whitespace-no-wrap {{ $overtime >= 0 ? ' text-green-500' : ' text-red-500' }}  {{ $date->copy()->endOfDay()->isFuture() ? ' invisible' : ' ' }}">
                                                                {{ overtime_show($overtime) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="mx-auto w-20 flex items-center justify-center text-center text-sm leading-none border border-transparent rounded-full relative">
                                                        <span class="px-0.5 py-1.5 text-gray-600 font-light">
                                                            @if($currentData && $currentData->check_out)
                                                                {{ trans('schedules.check_out.execuses.' . $currentData->check_out) }}
                                                            @else
                                                                X
                                                            @endif
                                                        </span>

                                                        @if ($overtime != 0 && ($profileCare || ($overtime < 0)))
                                                            <span class="pl-1 bg-white -ml-2 absolute left-full text-center text-xs whitespace-no-wrap {{ $overtime >= 0 ? ' text-green-500' : ' text-red-500' }}  {{ $date->copy()->endOfDay()->isFuture() ? ' invisible' : ' ' }}">
                                                                {{ overtime_show($overtime) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @php
                                                    if ($overtime != 0 && ($profileCare || ($overtime < 0))) {
                                                        $userTotalSchedule[$user->uuid]['overtime'] += $overtime;
                                                        $dateTotalSchedule[$date->format('Y-m-d')]['overtime'] += $overtime;
                                                    }
                                                @endphp
                                            </div>
                                        @else
                                            @if ($date->isSunday() && ! $loop->last)
                                                <div class="py-2.5 mx-4 border-l-2 border-dotted">
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach

                                    <div class="relative w-32 px-2.5 py-2.5"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex">
                        <div class="flex-1">
                            <div class="flex">
                                @foreach($datePeriod as $date)
                                    @if($date->isWeekday())
                                        <div class="px-2.5 py-2.5">
                                            <div class="mx-auto mb-2 w-20 flex items-center text-center text-sm leading-none border border-report-mantis rounded-full relative">
                                                <span class="px-1 py-1.5 flex-1">
                                                    {{ $dateTotalSchedule[$date->format('Y-m-d')]['available'] }}
                                                </span>

                                                @if ($profileCare || ($dateTotalSchedule[$date->format('Y-m-d')]['overtime'] < 0))
                                                    <span class="pl-1 bg-white -ml-2 absolute left-full text-center text-xs whitespace-no-wrap {{ $dateTotalSchedule[$date->format('Y-m-d')]['overtime'] >= 0 ? ' text-green-500' : ' text-red-500' }} {{ $date->copy()->endOfDay()->isFuture() ? ' invisible' : ' ' }}">
                                                        {{ overtime_show($dateTotalSchedule[$date->format('Y-m-d')]['overtime']) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="flex justify-center items-center">
                                                <div class="font-medium text-sm w-9 text-center border-black px-0.5 leading-none">{{ $dateTotalSchedule[$date->format('Y-m-d')]['breakfast'] }}</div>
                                                <div class=" border-l w-9 text-center font-medium text-sm border-black px-0.5 leading-none">{{ $dateTotalSchedule[$date->format('Y-m-d')]['lunch'] }}</div>
                                                <div class=" border-l w-9 text-center font-medium text-sm border-black px-0.5 leading-none">{{ $dateTotalSchedule[$date->format('Y-m-d')]['dinner'] }}</div>
                                            </div>
                                        </div>
                                    @else
                                        @if ($date->isSunday() && ! $loop->last)
                                            <div class="py-2.5 mx-4 border-l-2 border-dotted">
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 w-52 border-l pt-14">
                    @foreach($users as $user)
                        <div class="relative py-2.5 px-2 border-t flex items-center justify-center" wire:key="report-right-total-{{ $user->uuid }}">
                            <div class="relative mr-3 flex items-center text-sm leading-none text-center border border-report-mantis rounded-full w-11">
                                @if ($loop->first)
                                    <span class="absolute mb-4 translate-x-1/3 transform origin-top-left -rotate-45 font-normal text-xs bottom-full text-center">{{ trans('reports.presence') }}</span>
                                @endif

                                <span class="px-1 py-1.5 flex-1">{{ $userTotalSchedule[$user->uuid]['available'] }}</span>

                                @if ($profileCare || ($userTotalSchedule[$user->uuid]['overtime'] < 0))
                                    <span class="px-1 bg-white -mr-1 absolute right-full text-center text-xs whitespace-no-wrap {{ $userTotalSchedule[$user->uuid]['overtime'] >= 0 ? ' text-green-500' : ' text-red-500' }}">
                                        {{ overtime_show($userTotalSchedule[$user->uuid]['overtime']) }}
                                    </span>
                                @endif
                            </div>

                            {{-- <div class="ml-1.5 border-l text-sm font-bold border-black pl-0.5 leading-none w-8 text-center"> --}}
                            <div class="relative flex py-1.5 items-center text-sm leading-none text-center w-11">
                                @if ($loop->first)
                                    <span class="absolute mb-4 translate-x-1/4 transform origin-top-left -rotate-45 font-normal text-xs bottom-full text-center">{{ trans('reports.breakfast') }}</span>
                                @endif

                                <span class="px-1 border-l border-black flex-1 font-medium">{{ $userTotalSchedule[$user->uuid]['breakfast'] }}</span>
                            </div>

                            <div class="relative flex py-1.5 items-center text-sm leading-none text-center w-11">
                                @if ($loop->first)
                                    <span class="absolute mb-4 translate-x-1/4 transform origin-top-left -rotate-45 font-normal text-xs bottom-full text-center">{{ trans('reports.lunch') }}</span>
                                @endif

                                <span class="px-1 flex-1 font-medium border-l border-black">{{ $userTotalSchedule[$user->uuid]['lunch'] }}</span>
                            </div>

                            <div class="relative flex py-1.5 items-center text-sm leading-none text-center w-11">
                                @if ($loop->first)
                                    <span class="absolute mb-4 translate-x-1/4 transform origin-top-left -rotate-45 font-normal text-xs bottom-full text-center">{{ trans('reports.dinner') }}</span>
                                @endif

                                <span class="px-1 flex-1 font-medium border-l border-black">{{ $userTotalSchedule[$user->uuid]['dinner'] }}</span>
                            </div>
                        </div>
                    @endforeach

                    <div class="py-2.5 px-2 pt-9 mt-2 border-t flex items-center justify-center">
                        <div class="w-11 mr-3 text-center font-medium border-black leading-none text-report-mantis">{{ collect($userTotalSchedule)->sum('available') }}</div>
                        <div class="w-11 text-sm text-center border-l font-medium border-black pl-0.5 leading-none">{{ collect($userTotalSchedule)->sum('breakfast') }}</div>
                        <div class="w-11 text-sm text-center border-l font-medium border-black pl-0.5 leading-none">{{ collect($userTotalSchedule)->sum('lunch') }}</div>
                        <div class="w-11 text-sm text-center border-l font-medium border-black pl-0.5 leading-none">{{ collect($userTotalSchedule)->sum('dinner') }}</div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('users.title_lower')]) }}
        </x-no-data-found>
    @endif
</div>
