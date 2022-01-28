<div
    class="bg-white overflow-hidden shadow-md border-t border-gray-200 rounded-lg"
    x-data="{
        viewType: 'date',
        currentTime: '',
        toggleView() {
            if (this.viewType == 'date') {
                this.viewType = 'calendar'
            } else {
                this.viewType = 'date'
            }
        },
        updateCurrentTime() {
            var date = new Date();

            var hour = date.getHours();
            var minute = date.getMinutes();

            if (hour < 10) {
                hour = '0' + hour;
            }

            if (minute < 10) {
                minute = '0' + minute;
            }

            this.currentTime = hour + ':' + minute;
        },
    }"
    x-init="
        updateCurrentTime();
        setInterval(() => {updateCurrentTime()}, 10 * 1000);
    "
>
    <div style="min-height: 212px;" x-show="viewType == 'date'" class="text-center px-4 py-6 text-calendar-light flex items-center justify-center">
        <div>
            <div class="text-7xl leading-none font-bold mb-2" x-text="currentTime"></div>

            @php
                $now = now();
                $dateFormat = $now->dayName;
                $dateFormat .= ', ';
                $dateFormat .= $now->day;
                $dateFormat .= '. ';
                $dateFormat .= $now->monthName;
            @endphp

            <div class="font-bold text-xl xl:text-2xl leading-none">
                {{ $dateFormat }}
            </div>
        </div>
    </div>

    {{-- <div style="min-height: 212px;" x-show="viewType == 'calendar'" class="text-center px-4 py-1 text-calendar">
        @livewire('components.calendar')
    </div> --}}

    <div class="bg-gray-200 px-4 py-4 sm:px-6">
        <div class="text-sm leading-5 flex items-center justify-between">
            <a href="{{ route('calendar') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                {{ trans('dashboard.calendar_link') }}
            </a>

            {{-- <a href="#" x-on:click.prevent="toggleView" class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                {{ trans('dashboard.calendar_link') }}
            </a>

            <a x-show="viewType == 'calendar'" href="{{ route('calendar') }}" class="ml-4 font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                <x-heroicon-o-calendar class="w-5 h-5" />
            </a> --}}
        </div>
    </div>
</div>
