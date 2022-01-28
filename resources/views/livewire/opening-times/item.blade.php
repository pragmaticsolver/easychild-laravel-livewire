<div>
    @if ($this->key != 0)
        <hr class="max-w-xs mx-auto">
    @endif

    <div class="flex items-center flex-wrap justify-center">
        <div class="pt-3 pb-0 sm:pb-3 flex-shrink-0 w-full sm:w-10">
            <span class="block text-center text-sm text-gray-600 font-bold">{{ $this->shortWeekDayName }}</span>
        </div>

        <div class="py-3 mx-3 flex items-center justify-between">
            <div>
                <x-time-picker
                    wire:model="start"
                    label="XX:XX"
                    class="form-select select-field block"
                    start="05:00"
                    end="22:00"
                    wire-key="start-{{ $key }}"
                    :min-interval="30"
                ></x-time-picker>
            </div>

            <div class="mx-2 whitespace-no-wrap">{{ trans('openingtimes.text_to') }}</div>

            <div>
                <x-time-picker
                    wire:model="end"
                    label="XX:XX"
                    class="form-select select-field block"
                    start="05:00"
                    end="22:00"
                    wire-key="end-{{ $key }}"
                    :min-interval="30"
                ></x-time-picker>
            </div>
        </div>
    </div>
</div>
