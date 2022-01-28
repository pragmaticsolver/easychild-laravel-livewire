<div>
    <div class="mb-3">
        <div class="flex justify-between items-center">
            <div class="max-w-sm text-sm leading-4 font-medium text-gray-500">
                {{ $formattedDate }}
            </div>

            <div class="text-right">
                <button wire:click="prevDay" class="inline-flex items-center py-3 px-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    {{ trans('schedules.prev_btn') }}
                </button>

                <button wire:click="nextDay" class="ml-2 inline-flex items-center py-3 px-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    {{ trans('schedules.next_btn') }}
                </button>
            </div>
        </div>
    </div>

    @if($schedules->count())
        <div class="flex flex-col mb-5">
            <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    {{ trans('schedules.start') }}
                                </th>

                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    {{ trans('schedules.end') }}
                                </th>

                                @if (! auth()->user()->isUser())
                                <th class="px-6 py-3 text-center border-b border-gray-200 bg-gray-100 text-xs leading-4 font-medium text-gray-500">
                                    {{ trans('schedules.users') }}
                                </th>
                                @endif
                            </tr>
                        </thead>

                        <tbody class="bg-white">
                            @foreach($schedules as $schedule)
                                <tr wire:key="{{ $loop->index }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ $schedule['start'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ $schedule['end'] }}
                                    </td>

                                    @if (! auth()->user()->isUser())
                                    <td class="px-6 py-4 whitespace-no-wrap text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ $schedule['total'] }}
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('schedules.schedule_plural')]) }}
        </x-no-data-found>
    @endif
</div>
