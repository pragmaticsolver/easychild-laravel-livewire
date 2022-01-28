<tr>
    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
        <div class="flex items-center justify-items-start">
            <div class="mr-4 flex-shrink-0 w-8">
                <img src="{{ $this->user->avatar_url }}" alt="{{ $userName }}" class="w-8 h-8 rounded-full">
            </div>

            <div>
                <div class="text-sm leading-5 font-medium text-indigo-500">
                    <a href="{{ route('users.edit', $userUuid) }}" class="outline-none focus:outline-none">
                        {{ $userName }}
                    </a>
                </div>

                @if (auth()->user()->isManager())
                    <div class="text-sm leading-5 text-indigo-500">
                        <a href="{{ route('users.index', ['group' => $groupName]) }}" class="outline-none focus:outline-none">
                            {{ $groupName }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </td>

    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500">
            @if ($available)
                @if ($status == 'approved' && $start && $end)
                    {{ $start }} - {{ $end }}
                @else
                    {{ trans('group-class.availability.available') }}
                @endif
            @else
                {{ trans('group-class.availability.not-available') }}
            @endif
        </div>
    </td>

    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
        <div class="text-sm leading-5 text-gray-500" wire:key="{{ $presenceStart ?: 'start-null' }}-{{ $presenceEnd ?: 'end-null' }}">
            {{ $presenceStart ?: 'XX' }} - {{ $presenceEnd ?: 'XX' }}
        </div>
    </td>

    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
        <div class="flex items-center justify-start">
            @if ($this->isCurrentDay)
                <button
                    type="button"
                    title="{{ trans('schedules.tooltip_approve') }}"
                    {{ $this->startBtnEnabled() ? '' : 'disabled' }}
                    wire:click="setPresenceStart"
                    class="inline-flex items-center justify-between border-transparent rounded-full p-2 bg-indigo-600 text-white focus:outline-none mx-1 {{ $this->startBtnEnabled() ? 'cursor-pointer hover:bg-indigo-500' : 'opacity-50 cursor-default' }}"
                >
                    <x-heroicon-o-check class="w-4 h-4"></x-heroicon-o-check>
                </button>

                <button
                    type="button"
                    title="{{ trans('schedules.tooltip_decline') }}"
                    {{ $this->endBtnEnabled() ? '' : ' disabled ' }}
                    wire:click="showPresenceEndModal"
                    class="inline-flex items-center justify-between border-transparent rounded-full p-2 text-white bg-indigo-600 focus:outline-none mx-1 {{ $this->endBtnEnabled() ? 'cursor-pointer hover:bg-indigo-500' : 'opacity-50 cursor-default' }}"
                >
                    <x-heroicon-o-x class="w-4 h-4"></x-heroicon-o-x>
                </button>
            @endif

            @if (isset($uuid) && $uuid && $this->isPreviousDaySchedule() && $this->canEditSchedule())
                <button
                    type="button"
                    title="{{ trans('schedules.tooltip_edit') }}"
                    wire:click.prevent="$emitTo('group-class.index', 'editSchedulePresenceTime', '{{ $uuid }}')"
                    class="inline-flex items-center justify-between border-transparent rounded-full p-2 text-white bg-indigo-600 focus:outline-none mx-1 cursor-pointer hover:bg-indigo-500"
                >
                    <x-heroicon-o-pencil class="w-4 h-4" />
                </button>
            @endif
        </div>
    </td>
</tr>
