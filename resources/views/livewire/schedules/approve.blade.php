<div>
    <div class="mb-3">
        <div class="md:flex justify-between items-center">
            <div class="flex justify-between items-center flex-1">
                <x-search-box class="max-w-sm flex-1" :placeholder="$placeholder"></x-search-box>
            </div>

            <div class="w-2/5 pt-4 md:pt-0 text-right sm:pl-3 flex justify-between md:inline-flex items-center">
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

    <div class="flex flex-col mb-5 ">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <x-table-td-sort :text="trans('schedules.user')" :sort-enabled="true" :sort-by="$sortBy" :order="$sortOrder" name="users.given_names" :filters-list="getColumnFilters('text')" wire-key="user_name" class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"></x-table-td-sort>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.start') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.end') }}
                            </th>

                            <x-table-td-sort :text="trans('schedules.availability')" :sort-enabled="false" :sort-by="$sortBy" :order="$sortOrder" name="available" :filters-list="getColumnFilters('boolean')" :is-boolean-type="true" wire-key="available" class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500"></x-table-td-sort>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.status') }}
                            </th>

                            <x-table-td-sort :text="trans('schedules.breakfast')" :sort-enabled="false" :sort-by="$sortBy" :order="$sortOrder" name="available" :filters-list="getColumnFilters('boolean')" :is-boolean-type="true" wire-key="eats_onsite_breakfast" class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500"></x-table-td-sort>

                            <x-table-td-sort :text="trans('schedules.lunch')" :sort-enabled="false" :sort-by="$sortBy" :order="$sortOrder" name="available" :filters-list="getColumnFilters('boolean')" :is-boolean-type="true" wire-key="eats_onsite_lunch" class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500"></x-table-td-sort>

                            <x-table-td-sort :text="trans('schedules.dinner')" :sort-enabled="false" :sort-by="$sortBy" :order="$sortOrder" name="available" :filters-list="getColumnFilters('boolean')" :is-boolean-type="true" wire-key="eats_onsite_dinner" class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500" :drop-to-left="true"></x-table-td-sort>

                            {{-- <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.tooltip_save') }}
                            </th> --}}
                        </tr>
                    </thead>

                    @if(count($schedules))
                    <tbody class="bg-white">
                        @foreach($schedules as $schedule)
                        @livewire('schedules.approve-item', compact('schedule', 'openingTimes'), key($schedule['date'] . '-' . $schedule['user_uuid']))
                        @endforeach
                    </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if(! count($schedules))
    <x-no-data-found>
        {{ trans('pagination.not_found', ['type' => trans('schedules.schedule_plural')]) }}
    </x-no-data-found>
    @endif
</div>