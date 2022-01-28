<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <div class="mb-6 text-lg text-center text-gray-700">
        {{ trans('openingtimes.subtitle_opening_time') }}
    </div>

    @foreach ($openingTimes as $openingTime)
        @livewire('opening-times.item', compact('openingTime'), key($openingTime['key']))
    @endforeach
</div>
