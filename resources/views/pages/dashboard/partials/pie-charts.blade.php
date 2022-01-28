@php
    $width = 'lg:w-1/3';

    if (auth()->user()->isPrincipal()) {
        $width = 'xl:w-1/3';
    }
@endphp

{{-- time schedules --}}
<div class="mb-8 px-2 w-full md:w-1/2  {{ $width }}">
    <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
        {{ trans('dashboard.time_schedule_title') }}
    </h2>

    <div class="bg-white overflow-hidden shadow-md border-t border-gray-200 rounded-lg">
        <x-svg-pie
            :pieData="$timeSchedules"
            :totalText="trans('dashboard.child_total')"
            :currentText="trans('dashboard.child_present')"
            eventName="time-schedules"
            background="img/dashboard-img-03.svg"
        ></x-svg-pie>

        <div class="bg-gray-200 px-4 py-4 sm:px-6">
            <div class="text-sm leading-5">
                <a href="{{ route('schedules.index') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                    {{ trans('dashboard.show_schedules') }}
                </a>
            </div>
        </div>
    </div>
</div>

{{-- presence plan --}}
<div class="mb-8 px-2 w-full md:w-1/2  {{ $width }}">
    <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
        {{ trans('dashboard.presence_title') }}
    </h2>

    <div class="bg-white overflow-hidden shadow-md border-t border-gray-200 rounded-lg">
        <x-svg-pie
            :pieData="$prescenseSchedule"
            :totalText="trans('dashboard.presence_children_planned')"
            :currentText="trans('dashboard.presence_children_present')"
            eventName="prescense-schedules"
            background="img/dashboard-img-02.svg"
        ></x-svg-pie>

        <div class="bg-gray-200 px-4 py-4 sm:px-6">
            <div class="text-sm leading-5">
                <a href="{{ route('presence') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                    {{ trans('dashboard.show_attendance_list') }}
                </a>
            </div>
        </div>
    </div>
</div>

{{-- meal plan --}}
<div class="mb-8 px-2 w-full md:w-1/2  {{ $width }}">
    <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
        {{ trans('dashboard.meal_plan_title') }}
    </h2>

    <div class="bg-white overflow-hidden shadow-md border-t border-gray-200 rounded-lg">
        <x-meal-slider>
            @foreach($mealPlans as $key => $meal)
                <div :class="{'hidden': visibleItem != '{{ $key }}'}">
                    <x-svg-pie
                        :pieData="$meal"
                        :totalText="trans('dashboard.meal_plan_children_eating')"
                        :currentText="trans('dashboard.meal_plan_signed_out')"
                        eventName="meal-plan-{{ $key }}"
                        background="img/dashboard-img-01.svg"
                        :meal-plan="$key"
                    ></x-svg-pie>
                </div>
            @endforeach
        </x-meal-slider>

        <div class="bg-gray-200 px-4 py-4 sm:px-6">
            <div class="text-sm leading-5">
                <a href="{{ route('mealplan') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                    {{ trans('dashboard.show_meal_plan') }}
                </a>
            </div>
        </div>
    </div>
</div>
