<div>
    <div class="mb-3">
        <div class="md:flex justify-between items-center text-sm leading-5">
            <div class="flex-1 pr-4">
                @if ($group)
                {{ $group->name }} <br>
                @else
                {{ trans('extras.null_item_error', ['item' => trans('groups.title_singular')]) }} <br>
                @endif

                <strong>{{ $formattedDate }}</strong>

                <div>
                    {{ trans('group-class.index_page_total_users', ['number' => $totalUsers]) }} &nbsp / &nbsp; {{ trans('group-class.index_page_total_present_users', ['number' => $presentUsers]) }}
                </div>
            </div>
            <div class="w-2/5 pt-4 md:pt-0 text-right flex justify-between md:inline-flex items-center">
                <div class="mr-auto pr-4">
                    <x-pdf-download></x-pdf-download>
                </div>

                <button wire:click="prevDay" class="inline-flex items-center py-3 px-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    &nbsp;❮&nbsp;
                </button>

                <div class="w-full px-2 sm:w-1/2 -mt-1">
                    <div class="mt-1 rounded-md shadow-sm">
                        <x-date-picker name="date" wire:model="date"></x-date-picker>
                    </div>
                    @error('date')
                    <p class="mt-2 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <button wire:click="nextDay" class="ml-2 inline-flex items-center py-3 px-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    &nbsp;❯&nbsp;
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
                                {{ trans('group-class.child_name') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('group-class.schedule_time') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('group-class.presence_time') }}
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($schedules->count())
                        <tbody class="bg-white">
                            <tr>
                                <td colspan="4">
                                    @foreach($schedules as $schedule)
                                        @livewire('group-class.item', compact('schedule'), key($schedule['date'] . '-' . $schedule['user_uuid']))
                                    @endforeach
                                </td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @livewire('components.present-modal')

    @livewire('components.present-other-collect-modal')

    @if ($this->isCurrentDay && (! $showAll))
    <div class="flex justify-end">
        <a wire:click.prevent="showAllItems" href="#" class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
            {{ trans('group-class.show_all') }}
        </a>
    </div>
    @endif

    @if(! $schedules->count())
    <x-no-data-found>
        {{ trans('pagination.not_found', ['type' => trans('schedules.schedule_plural')]) }}
    </x-no-data-found>
    @endif

    <form wire:submit.prevent="submitScheduleUpdate">
        <x-jet-modal wire:model.defer="showEditScheduleForm" max-width="sm">
            <x-slot name="title">{{ trans('users.log_section.edit_log_modal_title') }}</x-slot>

            <x-slot name="content">
                <div>
                    <p>{{ trans('users.log_section.information') }}</p>
                </div>

                @if (isset($scheduleInEdit))
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.log_section.current_presence_status') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm bg-white inline-flex">
                        <span class="text-field">
                            {{ $presenceStartView }} - {{ $presenceEndView }}
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="mb-1">
                        <label for="start-time" class="inline-flex text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.log_section.new_presence_start_time') }}
                        </label>
                    </div>

                    <x-time-picker id="start-time" wire:model.defer="startTime" label="XX:XX" class="form-select select-field block w-24" :start="$minStartTime" :end="$maxEndTime"></x-time-picker>

                    @error('startTime')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <div class="mb-1">
                        <label for="end-time" class="inline-flex text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.log_section.new_presence_end_time') }}
                        </label>
                    </div>

                    <x-time-picker id="end-time" wire:model.defer="endTime" label="XX:XX" class="form-select select-field block w-24" :start="$minStartTime" :end="$maxEndTime"></x-time-picker>

                    @error('endTime')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif
            </x-slot>

            <x-slot name="footer">
                <div class="flex space-x-4">
                    <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                        {{ trans('users.log_section.submit_button') }}
                    </button>

                    <button type="button" wire:click.prevent="$set('showEditScheduleForm', false)" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed" wire:loading.attr="disabled">
                        {{ trans('users.log_section.cancel_button') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>
</div>
