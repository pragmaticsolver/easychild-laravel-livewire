@php
    $showTime = $this->userAvailability === 'not-available-with-time';
@endphp

<div
    x-data="createSchedule({
        showTime: Boolean('{{ $showTime }}') || false,
        disableSubmit: false,
        presentText: '{{ trans('schedules.availability_present') }}',
        absentText: '{{ trans('schedules.availability_absent') }}',
        notScheduled: '{{ trans('schedules.availability_not_scheduled') }}',
        schedule: {
            'available': Boolean('{{ $schedule->available }}'),
            'status': '{{ $schedule->status }}',
            'start': '{{ $schedule->start }}',
            'end': '{{ $schedule->end }}',
            'eats_onsite': {
                'breakfast': Boolean('{{ $schedule->eats_onsite['breakfast'] }}'),
                'lunch': Boolean('{{ $schedule->eats_onsite['lunch'] }}'),
                'dinner': Boolean('{{ $schedule->eats_onsite['dinner'] }}'),
            },
            'check_out': String('{{ $schedule->check_out }}') || null,
            'uuid': String('{{ $schedule->uuid }}') || null,
        },
        execuses: {
            ill: '{{ trans('schedules.check_out.execuses.ill') }}',
            vacation: '{{ trans('schedules.check_out.execuses.vacation') }}',
            cure: '{{ trans('schedules.check_out.execuses.cure') }}',
            other: '{{ trans('schedules.check_out.execuses.other') }}',
        },
        statuses: {
            approved: '{{ trans('schedules.status_approved') }}',
            declined: '{{ trans('schedules.status_declined') }}',
            pending: '{{ trans('schedules.status_pending') }}',
        },
        currentDate: '{{ $schedule->date }}',
        isDisabled: Boolean('{{ $this->isScheduleDisabled() }}') || false,
        isCurrentlyCheckedOut: Boolean('{{ $this->isCurrentlyCheckedOut() }}') || false,
        isMealUpdatesLocked: Boolean('{{ $this->isMealUpdatesLocked() }}') || false,
        checkOutTextConfig: {
            title: '{{ trans("schedules.check_out.confirmation.title") }}',
            description: '{{ trans("schedules.check_out.confirmation.description") }}',
        },
    }, @this)"
    x-init="onInit($watch, $dispatch, $wire)"
    x-on:schedules-create-item-refresh-check-out.window="refreshCheckOut($event.detail.dates, $event.detail.cause)"
    x-on:schedule-check-out-remove-{{ $schedule->date }}.window="schedule.check_out = null;@this.call('removeCheckOut', $event.detail)"
    x-on:schedule-update-{{ $currentChild->uuid }}-{{ $schedule->date }}.window="refreshCurrentData($event.detail)"
    class="py-2 -mx-1.5 sm:mx-0 {{ $isFirstInLoop ? ' pt-14' : '' }}"
>
    {{-- approved/pending/rejected circle box and date --}}
    <div class="flex items-center mb-2">
        <span
            class="group w-3 h-3 rounded-full mr-2 border border-gray-400 bg-green-500 relative cursor-pointer"
            x-bind:class="{
                'bg-green-500': schedule.status == 'approved',
                'bg-red-600': schedule.status == 'declined',
                'bg-yellow-300': schedule.status == 'pending',
            }"
        >
            <span
                class="capitalize group-hover:visible invisible whitespace-no-wrap absolute px-1 py-0.5 text-xs top-1/2 transform -translate-y-1/2 left-full ml-2 z-10 rounded-md"
                x-bind:class="{
                    'bg-green-400 text-white': schedule.status == 'approved',
                    'bg-red-400 text-white': schedule.status == 'declined',
                    'bg-yellow-400 text-gray-800': schedule.status == 'pending',
                }"
                x-text="statuses[schedule.status]"
            ></span>
        </span>

        <span class="text-sm text-gray-600 mr-2 w-16">{{ $this->displayText }}</span>
    </div>
    {{-- end --}}

    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-1 md:space-x-2">
            @foreach(['breakfast', 'lunch', 'dinner'] as $foodMeal)
                <span class="relative" wire:key="{{ $currentChild->uuid }}-{{ $foodMeal }}-{{ $schedule->date }}">
                    @if ($isFirstInLoop)
                        <span class="absolute origin-top-left transform -rotate-90 text-xs bottom-full left-1/4 mb-6">{{ trans('schedules.' . $foodMeal) }}</span>
                    @endif

                    <x-button-switch
                        :disabled="!$schedule->available || $this->isMealUpdatesLocked()"
                        title="{{ trans('schedules.' . $foodMeal) }}"
                        :not-editable="$this->isMealTypeDisabled($foodMeal)"
                        :alpine-var="'schedule.eats_onsite.' . $foodMeal"
                    >
                        <x-zondicon-location-food class="w-4 h-4" />
                    </x-button-switch>
                </span>
            @endforeach
        </div>

        {{-- time picker or available/not available switcher --}}
        <div
            class="mx-auto flex items-center justify-center text-xs sm:text-sm md:text-base py-0.5 h-11"
            x-bind:class="{
                'border-b': ! schedule.available,
            }"
        >
            @if ($showTime)
                <div x-show="schedule.available">
                    <x-time-picker
                        x-model="schedule.start"
                        ::disabled="isDisabled"
                        label="XX:XX"
                        :wire-key="$currentChild->uuid . '-start-' . $schedule->date"
                        class="form-select select-field block time-picker w-14 sm:w-20 text-xs sm:text-sm px-1 sm:px-2"
                        :start="$this->minMaxTime['min']"
                        :end="$this->minMaxTime['max']"
                    ></x-time-picker>
                </div>

                <div x-show="schedule.available" class="mx-2">{{ trans('openingtimes.text_to') }}</div>

                <div x-show="schedule.available" class="mr-2">
                    <x-time-picker
                        x-model="schedule.end"
                        ::disabled="isDisabled"
                        label="XX:XX"
                        :wire-key="$currentChild->uuid . '-end-' . $schedule->date"
                        class="form-select select-field block time-picker w-14 sm:w-20 text-xs sm:text-sm px-1 sm:px-2"
                        :start="$this->minMaxTime['min']"
                        :end="$this->minMaxTime['max']"
                    ></x-time-picker>
                </div>

                <span x-show="!schedule.available" x-text="schedule.check_out ? absentText : notScheduled"></span>
                <span x-show="!schedule.available && schedule.check_out" x-text="': ' + execuses[schedule.check_out]"></span>
            @else
                <span class="" x-text="schedule.available ? presentText : absentText"></span>
                <span x-show="schedule.check_out" x-text="': ' + execuses[schedule.check_out]"></span>
            @endif

            <button
                class="ml-2 inline-flex items-center border-transparent p-1 focus:outline-none text-yellow-500"
                x-bind:class="{
                    'cursor-pointer hover:text-yellow-600': !isDisabled,
                    'opacity-50 cursor-default': isDisabled,
                }"
                x-show="(!schedule.available && !schedule.check_out) || schedule.check_out"
                x-on:click.prevent="onCheckOutClickEvent($dispatch)"
                x-bind:disabled="isDisabled"
            >
                <x-zondicon-edit-pencil class="w-4 h-4" />
            </button>
        </div>
        {{-- end --}}

        <div class="relative">
            @if ($isFirstInLoop)
                <span class="absolute origin-top-left transform -rotate-90 text-xs bottom-full left-1/4 mb-6">{{ trans('schedules.availability_title') }}</span>
            @endif

            <x-switch
                alpine-var="schedule.available"
                ::disabled="isDisabled"
                x-on:click.prevent="(!isDisabled) ? (schedule.available = !schedule.available) : null"
                x-bind:class="{
                    'bg-gray-400': !schedule.available,
                    'bg-blue-600': schedule.available,
                    'cursor-default opacity-50': isDisabled,
                    'cursor-pointer': !isDisabled,
                }"
            />
        </div>
    </div>
</div>
