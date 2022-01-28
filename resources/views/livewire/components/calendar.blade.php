<div class="pb-3">
    {{-- month/year header --}}
    <div class="text-calendar-dark text-center font-bold uppercase">
        {{ $monthName }} {{ $year }}
    </div>

    {{-- week header --}}
    <div class="flex font-bold text-calendar-dark text-center capitalize text-sm mb-1">
        @foreach($weekList as $weekItem)
            <div class="flex-grow w-1/7">
                {{ $weekItem }}
            </div>
        @endforeach
    </div>

    {{-- calendar --}}
    <div class="flex flex-wrap items-center justify-start font-bold text-base leading-none text-black">
        @foreach($this->daysList as $dayItem)
            <div class="w-1/7 mt-1.5">
                <span class="{{ $dayItem['isCurrent'] ? 'block mx-auto bg-calendar-light rounded-full w-7 py-1 text-center border border-transparent' : '' }} {{ $dayItem['currentMonth'] ? '' : 'text-gray-500' }}">
                    {{ $dayItem['text'] }}
                </span>
            </div>
        @endforeach
    </div>
</div>
