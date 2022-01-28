@php
    $statusTypes = [
        'declined' => trans('schedules.status_declined'),
        'approved' => trans('schedules.status_approved'),
        'pending' => trans('schedules.status_pending'),
    ];
@endphp

<tr x-data="{
    disabled: @json($this->disabled)
}">
    <td class="px-6 py-4 border-b border-gray-200">
        <div class="whitespace-no-wrap text-sm leading-5 font-medium text-indigo-500">
            <a href="{{ route('users.edit', $user->uuid) }}" class="outline-none focus:outline-none">
                {{ $user->full_name }}
            </a>
        </div>

        <div class="whitespace-no-wrap text-sm leading-5 text-indigo-500">
            <a href="{{ route('users.index', ['group' => $groupName]) }}" class="outline-none focus:outline-none">
                {{ $groupName }}
            </a>
        </div>
    </td>

    <td class="px-4 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500 w-32 text-center">
        <div>
            @if ($this->available && $this->needTimeSchedule)
                <x-time-picker :disabled="$this->disabled" label="XX:XX" wire:model="start" class="form-select select-field w-20 px-2 time-picker block" :start="$min" :end="$max"></x-time-picker>
            @else
                -
            @endif
        </div>
    </td>

    <td class="px-4 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500 w-32 text-center">
        <div>
            @if ($this->available && $this->needTimeSchedule)
                <x-time-picker :disabled="$this->disabled" label="XX:XX" wire:model="end" class="form-select select-field w-20 px-2 time-picker block" :start="$min" :end="$max"></x-time-picker>
            @else
                -
            @endif
        </div>
    </td>

    <td class="px-6 py-4 whitespace-no-wrap text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
        @if (! $this->needTimeSchedule || ! $this->available)
            <x-switch :disabled="$this->disabled" wire:model="available"></x-switch>
        @endif
    </td>

    <td class="px-6 py-4 whitespace-no-wrap text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
        <div>
            <x-approve-switch :disabled="$this->disabled" :default-value="$status" wire:model="status" :options="$statusTypes"></x-approve-switch>
            {{-- @if ($this->needTimeSchedule)
            @else
                <x-approve-switch :disabled="true" default-value="approved" :options="$statusTypes"></x-approve-switch>
            @endif --}}
        </div>
    </td>

    @foreach(['breakfast', 'lunch', 'dinner'] as $mealItem)
        <td wire:key="{{ $date }}-{{ $user->uuid }}-{{ $mealItem }}" class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500 text-center">
            <x-approve-meal-switch
                :is-disabled="$this->disabled || ! $this->available"
                :default-value="$eatsOnsite[$mealItem]"
                wire:click.prevent="$toggle('eatsOnsite.{{ $mealItem }}')"
                :color-class="$this->available ? '' : 'sm:bg-red-500'"
                :not-editable="$this->isMealTypeDisabled($mealItem)"
                :disabled="($this->disabled || ! $this->available) || $this->isMealTypeDisabled($mealItem)"
            >
                <x-zondicon-location-food class="w-4 h-4" />
            </x-approve-meal-switch>
        </td>
    @endforeach
</tr>
