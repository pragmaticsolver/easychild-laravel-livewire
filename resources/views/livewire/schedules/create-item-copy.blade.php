@php
    $showTime = $this->userAvailability === 'not-available-with-time';
@endphp

<div
    @if($showTime)
        x-data="{
            start: @entangle('schedule.start').defer,
            end: @entangle('schedule.end').defer,
            checkedOut: @entangle('schedule.check_out'),

            currentDate: '{{ $schedule->date }}',
            refreshCheckOut($dates, cause) {
                if ($dates.includes(this.currentDate)) {
                    @this.call('updateCheckOut', this.currentDate, cause)
                }
            },

            checkIfCanSubmit() {
                if (this.start == 'XX:XX' || this.end == 'XX:XX') {
                    return;
                }

                if (!this.start || !this.end) {
                    return;
                }

                if (this.checkedOut) {
                    return;
                }

                @this.call('saveSchedule', 'startOrEnd');
            },
        }"
        x-init="
            $watch('start', val => checkIfCanSubmit());
            $watch('end', val => checkIfCanSubmit());
        "
    @else
        x-data="{
            currentDate: '{{ $schedule->date }}',
            refreshCheckOut($dates, cause) {
                if ($dates.includes(this.currentDate)) {
                    @this.call('updateCheckOut', this.currentDate, cause)
                }
            }
        }"
    @endif
    x-on:schedule-check-out-{{ $schedule->date }}.window="@this.call('removeCheckOut', $event.detail)"
    x-on:schedules-create-item-refresh-check-out.window="refreshCheckOut($event.detail.dates, $event.detail.cause)"
    class="flex items-center justify-center py-2 sm:py-1 sm:justify-between flex-wrap"
>
    <div class="hidden sm:flex items-center py-1">
        {{-- @if ($showTime) --}}
            @switch($schedule->status)
                @case('approved')
                    <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-green-500"></span>
                    @break
                @case('declined')
                    <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-red-600"></span>
                    @break
                @case('pending')
                    <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-yellow-300"></span>
                    @break
                @default
                    <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-gray-300"></span>
            @endswitch
        {{-- @endif --}}

        <span class="text-sm text-gray-600 mr-2 w-16">{{ $this->displayText }}</span>
    </div>

    <div class="flex items-end sm:items-center justify-center sm:flex-1">
        <div class="py-1 sm:ml-auto sm:mr-auto">
            <div class="flex items-center py-1 sm:hidden">
                @if ($showTime)
                    @switch($schedule->status)
                        @case('approved')
                            <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-green-500"></span>
                            @break
                        @case('declined')
                            <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-red-600"></span>
                            @break
                        @case('pending')
                            <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-yellow-300"></span>
                            @break
                        @default
                            <span class="w-3 h-3 rounded-full mr-2 border border-gray-400 bg-gray-300"></span>
                    @endswitch
                @endif

                <span class="text-sm text-gray-600 mr-2 w-16">{{ $this->displayText }}</span>
            </div>

            <div class="flex items-center justify-center text-xs sm:text-sm md:text-base">
                @if ($showTime)
                    <div>
                        <x-time-picker
                            x-model="start"
                            :disabled="$this->isScheduleDisabled()"
                            label="XX:XX"
                            :wire-key="'start-' . $schedule->date"
                            class="form-select select-field block time-picker w-14 sm:w-20 text-xs sm:text-sm px-1 sm:px-2"
                            :start="$this->minMaxTime['min']"
                            :end="$this->minMaxTime['max']"
                        ></x-time-picker>
                    </div>

                    <div class="mx-2">{{ trans('openingtimes.text_to') }}</div>

                    <div class="mr-2">
                        <x-time-picker
                            x-model="end"
                            :disabled="$this->isScheduleDisabled()"
                            label="XX:XX"
                            :wire-key="'end-' . $schedule->date"
                            class="form-select select-field block time-picker w-14 sm:w-20 text-xs sm:text-sm px-1 sm:px-2"
                            :start="$this->minMaxTime['min']"
                            :end="$this->minMaxTime['max']"
                        ></x-time-picker>
                    </div>
                @else
                    <span class="mr-3">{{ trans('schedules.availability') }}</span>

                    <span
                        x-on:input-is-changed-{{ $schedule->date }}.debounce.150ms="@this.call('saveSchedule', 'available')"
                    >
                        <x-switch
                            wire:model.defer="schedule.available"
                            dispatch-event="input-is-changed-{{ $schedule->date }}"
                            :disabled="$this->isScheduleDisabled()"
                        />
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center justify-center space-x-1 md:space-x-2 px-2 {{ $showTime ? ' py-2' : 'py-1' }}">
            <span
                @if ($showTime)
                    x-on:input="checkIfCanSubmit()"
                @else
                    x-on:input="@this.call('saveSchedule', 'meal')"
                @endif

                @if ($isFirstInLoop)
                    class="relative food-label"
                    data-food-label="{{ trans('schedules.breakfast') }}"
                @endif
            >
                <x-button-switch
                    :disabled="!$schedule->available || $this->isMealUpdatesLocked()"
                    :default-value="$schedule->available ? $schedule->eats_onsite['breakfast'] : false"
                    wire:model.defer="schedule.eats_onsite.breakfast"
                    title="{{ trans('schedules.breakfast') }}"
                    :not-editable="$this->isMealTypeDisabled('breakfast')"
                >
                    <x-zondicon-location-food class="w-4 h-4" />
                </x-button-switch>
            </span>

            <span
                @if ($showTime)
                    x-on:input="checkIfCanSubmit()"
                @else
                    x-on:input="@this.call('saveSchedule', 'meal')"
                @endif

                @if ($isFirstInLoop)
                    class="relative food-label"
                    data-food-label="{{ trans('schedules.lunch') }}"
                @endif
            >
                <x-button-switch
                    :disabled="!$schedule->available || $this->isMealUpdatesLocked()"
                    :default-value="$schedule->available ? $schedule->eats_onsite['lunch'] : false"
                    wire:model.defer="schedule.eats_onsite.lunch"
                    title="{{ trans('schedules.lunch') }}"
                    :not-editable="$this->isMealTypeDisabled('lunch')"
                >
                    <x-zondicon-location-food class="w-4 h-4" />
                </x-button-switch>
            </span>

            <span
                @if ($showTime)
                    x-on:input="checkIfCanSubmit()"
                @else
                    x-on:input="@this.call('saveSchedule', 'meal')"
                @endif

                @if ($isFirstInLoop)
                    class="relative food-label"
                    data-food-label="{{ trans('schedules.dinner') }}"
                @endif
            >
                <x-button-switch
                    :disabled="!$schedule->available || $this->isMealUpdatesLocked()"
                    :default-value="$schedule->available ? $schedule->eats_onsite['dinner'] : false"
                    wire:model.defer="schedule.eats_onsite.dinner"
                    title="{{ trans('schedules.dinner') }}"
                    :not-editable="$this->isMealTypeDisabled('dinner')"
                >
                    <x-zondicon-location-food class="w-4 h-4" />
                </x-button-switch>
            </span>

            {{-- Check out --}}
            <span
                @if ($isFirstInLoop)
                    class="relative food-label"
                    data-food-label="{{ trans('schedules.check_out.main_label') }}"
                @endif
            >
                <button
                    class="inline-flex items-center border-transparent rounded-full p-1.5 sm:p-2 focus:outline-none text-white"
                    x-data="{
                        isDisabled: Boolean('{{ $this->isScheduleDisabled() }}') || false,
                        checkedOut: @entangle('schedule.check_out'),

                        onClickEvent($dispatch) {
                            if (this.checkedOut) {
                                $dispatch('schedule-check-out-confirm-modal-open', {
                                    'title': '{{ trans("schedules.check_out.confirmation.title") }}',
                                    'description': '{{ trans("schedules.check_out.confirmation.description") }}',
                                    'event': 'schedule-check-out-{{ $schedule->date }}',
                                    'uuid': '{{ $schedule->uuid }}',
                                });
                            } else {
                                $dispatch('show-check-out-modal', '{{ $schedule->date }}');
                            }
                        },
                    }"
                    x-bind:class="{
                        'cursor-pointer': !isDisabled,
                        'bg-red-500': !!checkedOut,
                        'bg-yellow-500': !checkedOut,
                        'hover:bg-red-600': !!checkedOut && !isDisabled,
                        'hover:bg-yellow-600': !checkedOut && !isDisabled,
                        'opacity-50 cursor-default': isDisabled,
                    }"
                    x-on:click.prevent="onClickEvent($dispatch)"
                    x-bind:disabled="isDisabled"
                >
                    <x-zondicon-trash class="w-4 h-4" />
                </button>
            </span>
        </div>
    </div>
</div>
