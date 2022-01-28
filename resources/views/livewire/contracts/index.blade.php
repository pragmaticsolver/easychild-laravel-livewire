<div
    x-on:delete-contract.window="@this.call('delete', $event.detail)"
>
    <div class="mb-3">
        <div class="sm:flex justify-between">
            <x-search-box class="max-w-sm flex-1" :placeholder="$placeholder"></x-search-box>

            <div class="pt-4 w-full sm:w-auto sm:pt-0 text-right sm:pl-3 flex justify-between sm:inline-flex items-center">
                <div class="mr-4">
                    <x-pdf-download></x-pdf-download>
                </div>

                <a href="#" wire:click.prevent="createNew" class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="-ml-0.5 mr-2 h-4 w-4" />
                    {{ trans('organizations.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="flex flex-col mb-3">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <x-table-td-sort
                                :text="trans('contracts.title_label')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="title"
                                :filters-list="getColumnFilters('text')"
                                wire-key="contract_title"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500 whitespace-no-wrap">
                                <x-sort-column
                                    :text="trans('contracts.time_per_day_table', ['type' => trans('contracts.hour')])"
                                    :sort-by="$sortBy"
                                    name="time_per_day"
                                    :order="$sortOrder"
                                ></x-sort-column>
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500 whitespace-no-wrap">
                                <x-sort-column
                                    :text="trans('contracts.overtime_table', ['type' => trans('contracts.minute')])"
                                    :sort-by="$sortBy"
                                    name="overtime"
                                    :order="$sortOrder"
                                ></x-sort-column>
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500 whitespace-no-wrap">
                                {{ trans('contracts.bring_until') }}
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500 whitespace-no-wrap">
                                {{ trans('contracts.collect_until') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($contracts->count())
                        <tbody class="bg-white">
                            @foreach($contracts as $contract)
                                <tr wire:key="{{ $contract->uuid }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <div class="text-sm leading-5 text-gray-500">{{ $contract->title }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm text-center leading-5 text-gray-500">
                                        {{ $contract->time_per_day }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm text-center leading-5 text-gray-500">
                                        {{ $contract->overtime }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm text-center leading-5 text-gray-500">
                                        {{ $contract->bring_until_formatted ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm text-center leading-5 text-gray-500">
                                        {{ $contract->collect_until_formatted ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center justify-end">
                                            <a title="{{ trans('contracts.edit') }}" href="#" wire:click.prevent="edit('{{ $contract->uuid }}')" class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5"></x-heroicon-o-pencil>
                                            </a>

                                            <a
                                                title="{{ trans('contracts.delete_top_title') }}" href="#"
                                                class="text-indigo-600 hover:text-indigo-900 ml-3"
                                                x-on:click.prevent="$dispatch('contract-delete-confirm-modal-open', {
                                                    'title': '{{ trans("contracts.delete_top_title") }}',
                                                    'description': '{{ trans("contracts.delete_title", ["title" => $contract->title]) }}',
                                                    'event': 'delete-contract',
                                                    'uuid': '{{ $contract->uuid }}',
                                                })"
                                            >
                                                <x-heroicon-o-trash class="w-5 h-5"></x-heroicon-o-trash>
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

    @if(! $contracts->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('contracts.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $contracts->links() }}
    </div>

    <form wire:submit.prevent="submit">
        <x-jet-modal wire:model.defer="showModal">
            <x-slot name="title">{{ trans('contracts.add_modal_title') }}</x-slot>

            <x-slot name="content">
                <div>
                    <label for="contract.title" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('contracts.title_label') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.defer="contract.title" id="contract.title" name="contract.title" type="text" required autocomplete="off" class="text-field @error('contract.title') error @enderror" />
                    </div>

                    @error('contract.title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex flex-wrap -mx-2">
                    <div class="px-2 w-full sm:w-1/2">
                        <label for="contract.time_per_day" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contracts.time_per_day', ['type' => trans('contracts.hour')]) }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contract.time_per_day" id="contract.time_per_day" name="contract.time_per_day" type="number" step=".1" required autocomplete="off" class="text-field @error('contract.time_per_day') error @enderror" />
                        </div>

                        @error('contract.time_per_day')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-2 w-full mt-6 sm:mt-0 sm:w-1/2">
                        <label for="contract.overtime" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contracts.overtime', ['type' => trans('contracts.minute')]) }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contract.overtime" id="contract.overtime" name="contract.overtime" type="number" autocomplete="off" class="text-field @error('contract.overtime') error @enderror" />
                        </div>

                        @error('contract.overtime')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap -mx-2">
                    <div class="px-2 w-full sm:w-1/2">
                        <label for="contract.bring_until" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contracts.bring_until') }}
                        </label>

                        <x-time-picker
                            wire:model.defer="contract.bring_until"
                            label="XX:XX"
                            class="form-select select-field block w-24"
                            start="00:00"
                            end="23:30"
                        ></x-time-picker>

                        @error('contract.bring_until')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="px-2 w-full mt-6 sm:mt-0 sm:w-1/2">
                        <label for="contract.collect_until" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contracts.collect_until') }}
                        </label>

                        <x-time-picker
                            wire:model.defer="contract.collect_until"
                            label="XX:XX"
                            class="form-select select-field block w-24"
                            start="00:00"
                            end="23:30"
                        ></x-time-picker>

                        @error('contract.collect_until')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <div class="flex space-x-4">
                    <button
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('contracts.submit') }}
                    </button>

                    <button
                        type="button"
                        wire:click.prevent="$set('showModal', false)"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('contracts.cancel') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>

    <x-confirm-modal confirm-id="contract-delete"></x-confirm-modal>
</div>
