<div class="sm:mx-auto sm:w-full sm:max-w-lg">
    <div class="mb-6 text-gray-700">
        <h1 class="text-lg">
            {{ $organization->name }}
        </h1>
        <h2 class="text-sm">
            {{ $currentChild->full_name }} - {{ $group ? $group->name : 'N/A' }}
        </h2>
    </div>

    @foreach ($schedules as $schedule)
        @if (empty($schedule))
            @if (!$loop->last)
                <hr class="max-w-xs mx-auto my-4">
            @endif
        @else
            @php
                $firstInLoop = $loop->first;
            @endphp

            @livewire('schedules.create-item', compact('schedule', 'currentChild', 'firstInLoop'), key($currentChild->uuid . '-' .$schedule['date']))
        @endif
    @endforeach

    {{-- <x-confirm-modal confirm-id="schedule-delete"></x-confirm-modal> --}}
    {{-- <x-confirm-modal confirm-id="schedule-check-out"></x-confirm-modal> --}}

    @livewire('schedules.check-out')
</div>
