<div>
    <div class="mb-3">
        <div class="md:flex justify-between items-center">
            <div class="flex justify-between items-center flex-1">
                <x-search-box class="max-w-sm flex-1" :placeholder="$placeholder"></x-search-box>

                <div class="max-w-sm text-sm leading-4 font-medium text-gray-500 md:mr-auto ml-4">
                    {{ $date }}
                </div>
            </div>

            <div class="pt-4 md:pt-0 text-right sm:pl-3 flex justify-between md:inline-flex items-center">
                {{-- <div class="mr-auto pr-4">
                    <x-pdf-download></x-pdf-download>
                </div> --}}

                <button wire:click="prevDay" class="inline-flex items-center py-3 px-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    {{ trans('schedules.prev_btn') }}
                </button>

                <button wire:click="nextDay" class="ml-2 inline-flex items-center py-3 px-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    {{ trans('schedules.next_btn') }}
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.user_group_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.date_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.logs_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if ($schedules->count())
                        <tbody class="bg-white">
                            @foreach($schedules as $schedule)
                                <tr wire:key="{{ $schedule->uuid }}">
                                    <td class="px-6 py-4 border-b border-gray-200 text-left text-sm leading-5 text-gray-500">
                                        <div class="whitespace-no-wrap text-sm leading-5 font-medium text-indigo-500">
                                            <a href="{{ route('users.edit', $schedule->user_uuid) }}" class="outline-none focus:outline-none">
                                                {{ $schedule->user_name }}
                                            </a>
                                        </div>

                                        <div class="whitespace-no-wrap text-sm leading-5 text-indigo-500">
                                            @if ($schedule->group_name && $schedule->group_uuid)
                                                <a href="{{ route('groups.edit', $schedule->group_uuid) }}" class="outline-none focus:outline-none">
                                                    {{ $schedule->group_name }}
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-left border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        <div class="text-sm leading-5 text-gray-500">{{ $schedule->date }}</div>
                                    </td>

                                    <td class="px-6 py-4 text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        <div class="text-sm leading-5 text-gray-500">{{ $schedule->attendances }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex justify-end items-center">
                                            <a title="{{ trans('attendances.view_events') }}" wire:click.prevent="showEvents('{{ $schedule->uuid }}')" href="#" class="ml-3 rounded-full text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-eye class="w-5 h-5"></x-heroicon-o-eye>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if(! $schedules->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('attendances.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $schedules->links() }}
    </div>

    <x-jet-modal wire:model.defer="showModal">
        <x-slot name="title">{{ trans('attendances.modal_title') }}</x-slot>

        <x-slot name="content">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-2 py-1 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ trans('attendances.event_title') }}
                        </th>

                        <th class="px-2 py-2 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ trans('attendances.by_title') }}
                        </th>

                        <th class="px-2 py-2 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ trans('attendances.date_time_title') }}
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200" x-max="1">
                    @forelse($events as $event)
                        <tr class="bg-white" wire:key="'{{ $event->identifier }}'">
                            <td class="px-2 py-2 text-left whitespace-nowrap text-sm text-gray-500">
                                @if ($event->type == 'enter')
                                    {{ trans('attendances.event_type.enter') }}
                                @else
                                    {{ trans('attendances.event_type.leave') }}
                                @endif
                            </td>

                            <td class="px-2 py-2 text-left whitespace-nowrap text-sm text-gray-500">
                                @if ($event->trigger_person_name)
                                    {{ $event->trigger_person_name }}
                                @else
                                    @if ($event->trigger_type == 'auto')
                                        {{ trans('attendances.trigger_type.auto') }}
                                    @else
                                        {{ trans('attendances.trigger_type.terminal') }}
                                    @endif
                                @endif
                            </td>

                            <td class="px-2 py-2 text-right whitespace-nowrap text-sm text-gray-500">
                                {{ $event->created_at->format(config('setting.format.datetime')) }}

                                <strong>
                                    @if ($event->type == 'leave' && ! $loop->first)
                                        ({{ number_format($event->created_at->floatDiffInHours($events[$loop->index - 1]->created_at), 1) }} hr)
                                    @endif
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white" wire:key="no-items-found">
                            <td colspan="3" class="px-2 py-2 text-left whitespace-nowrap text-sm text-gray-500">
                                <x-no-data-found>
                                    {{ trans('pagination.not_found', ['type' => trans('attendances.title_lower')]) }}
                                </x-no-data-found>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-slot>
    </x-jet-modal>
</div>
