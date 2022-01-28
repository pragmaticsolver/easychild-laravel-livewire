@php
    $mealTypes = ['breakfast', 'lunch', 'dinner'];
    $totalMealValue = [
        'breakfast' => 0,
        'lunch' => 0,
        'dinner' => 0,
    ];
@endphp

<div>
    <div class="mb-3">
        <div class="sm:flex justify-between items-center">
            <div class="mb-4 sm:mb-0 max-w-sm text-sm leading-4 font-medium text-gray-500">
                {{ $date }}
            </div>

            <div class="w-full sm:w-auto text-right sm:pl-3">
                <div class="rounded-md flex items-center">
                    <button
                        type="button"
                        title="{{ trans('vendors.previous') }}"
                        wire:click.prevent="previousDate"
                        class="inline-flex items-center justify-between border-transparent rounded-full p-2 text-white bg-green-500 focus:outline-none cursor-pointer"
                    >
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>

                    @if (! auth()->user()->isVendor())
                        <select wire:model="viewSetting" id="view-type" name="view-setting" class="form-select select-field ml-2">
                            <option value="all">{{ trans('users.vendor_view_all') }}</option>
                            <option value="summary">{{ trans('users.vendor_view_summary') }}</option>
                        </select>
                    @endif

                    <select wire:model="viewType" id="view-type" name="view-type" class="form-select select-field ml-2">
                        <option value="daily">{{ trans('vendors.daily') }}</option>
                        <option value="weekly">{{ trans('vendors.weekly') }}</option>
                        <option value="monthly">{{ trans('vendors.monthly') }}</option>
                    </select>

                    <button
                        type="button"
                        title="{{ trans('vendors.next') }}"
                        {{ $this->nextDisabled ? ' disabled' : '' }}
                        wire:click.prevent="nextDate"
                        class="ml-2 inline-flex items-center justify-between border-transparent rounded-full p-2 text-white bg-green-500 focus:outline-none {{ $this->nextDisabled ? ' opacity-50 cursor-default' : ' cursor-pointer' }}"
                    >
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    @if ($viewSetting == 'summary')
                        <thead>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('vendors.group') }}
                            </th>
                            {{-- <x-table-td-sort
                                :text="trans('vendors.group')"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="group_name"
                                :filters-list="getColumnFilters('text')"
                                wire-key="user_group_name"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                                :visible="true"
                                :sort-enabled="true"
                            ></x-table-td-sort> --}}

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.breakfast') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.lunch') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('schedules.dinner') }}
                            </th>
                        </thead>
                    @else
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs w-10 leading-4 font-medium text-gray-500">
                                    {{ trans('vendors.serial_no') }}
                                </th>

                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    <x-sort-column
                                        :text="trans('vendors.given_names')"
                                        :sort-by="$sortBy"
                                        name="given_names"
                                        :order="$sortOrder"
                                    ></x-sort-column>
                                </th>

                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    <x-sort-column
                                        :text="trans('vendors.last_name')"
                                        :sort-by="$sortBy"
                                        name="last_name"
                                        :order="$sortOrder"
                                    ></x-sort-column>
                                </th>


                                @if ($viewType == 'daily')
                                    <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-xs text-center leading-4 font-medium text-gray-500">
                                        {{ trans('vendors.eats_onsite') }}
                                    </th>
                                @else
                                    <th class="px-4 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                        {{ trans('schedules.breakfast') }}
                                    </th>
                                    <th class="px-4 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                        {{ trans('schedules.lunch') }}
                                    </th>
                                    <th class="px-4 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                        {{ trans('schedules.dinner') }}
                                    </th>
                                @endif

                                <x-table-td-sort
                                    :text="trans('vendors.group')"
                                    :sort-by="$sortBy"
                                    :order="$sortOrder"
                                    name="group_name"
                                    :filters-list="getColumnFilters('text')"
                                    wire-key="user_group_name"
                                    class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                                    :visible="true"
                                    :sort-enabled="true"
                                ></x-table-td-sort>

                                @if ($viewType === 'daily')
                                    <th class="px-6 py-3 text-left border-b border-gray-200 bg-gray-100 text-xs leading-4 font-medium text-gray-500">
                                        {{ trans('vendors.allergy') }}
                                    </th>
                                @endif
                            </tr>
                        </thead>
                    @endif

                    @if(count($schedules))
                        <tbody class="bg-white">
                            @if ($viewSetting == 'summary')
                                @foreach($schedules as $schedule)
                                    <tr wire:key="{{ $viewType . '-' . $viewDate . '-' . $schedule['group_name'] }}">
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            {{ $schedule['group_name'] }}
                                        </td>

                                        @foreach($mealTypes as $meal)
                                            <td class="px-6 py-4 whitespace-no-wrap text-center border-b border-gray-200 leading-5 text-gray-500" wire:key="{{ $viewType . '-' . $viewDate . '-' . $schedule['group_name'] . '-' . $meal }}">
                                                <div class="text-lg font-bold">{{ $schedule['total_' . $meal] }}</div>

                                                @if (Arr::has($schedule['allergies'], $meal))
                                                    @foreach($schedule['allergies'][$meal] as $allergyItem => $mealTotal)
                                                        <div class="text-sm font-bold" wire:key="{{ $viewType . '-' . $viewDate . '-' . $schedule['group_name'] . '-' . $meal . '-' . $allergyItem }}">
                                                            {{ $allergyItem }} ({{ $mealTotal }}x)
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                @foreach($schedules as $data)
                                    <tr wire:key="{{ $viewType . '-' . $viewDate . '-' . ($viewType == 'daily' ? $data['uuid'] : $data['ukey']) }}">
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            {{ $loop->index + 1 }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            {{ $data['given_names'] }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            {{ $data['last_name'] }}
                                        </td>

                                        @if ($viewType === 'daily')
                                            <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-center text-sm leading-5 text-gray-500">
                                                @foreach($mealTypes as $meal)
                                                    @php
                                                        $currentValue = $data->eats_onsite[$meal];

                                                        if (is_null($currentValue)) {
                                                            $currentValue = $this->eatsOnsiteOrgDefaults[$meal];
                                                        }

                                                        if ($currentValue) {
                                                            $totalMealValue[$meal]++;
                                                        }
                                                    @endphp

                                                    <span
                                                        class="inline-flex items-center justify-center p-1.5 w-8 text-center pointer-events-none rounded-full focus:outline-none text-white"
                                                        wire:key="{{ $viewType . '-' . $viewDate }}-{{ $data->uuid }}-{{ $meal }}"
                                                        x-bind:class="{
                                                            'bg-green-400': Boolean('{{ $currentValue }}'),
                                                            'bg-red-500': !Boolean('{{ $currentValue }}'),
                                                        }"
                                                    >
                                                        <span class="flex-1">
                                                            {{ trans("vendors.abbr.{$meal}") }}
                                                        </span>
                                                    </span>
                                                @endforeach
                                            </td>
                                        @else
                                            @foreach($mealTypes as $meal)
                                                @php
                                                    $totalMealValue[$meal] += $data['total_' . $meal];
                                                @endphp

                                                <td class="px-4 py-4 whitespace-no-wrap text-center border-b border-gray-200 leading-5 text-gray-500" wire:key="{{ $viewType . '-' . $viewDate . '-' . ($viewType == 'daily' ? $data['uuid'] : $data['ukey']) . '-' . $meal }}">
                                                    <div class="text-lg font-bold">{{ $data['total_' . $meal] }}</div>

                                                    @if (Arr::has($data['allergies'], $meal))
                                                        @foreach($data['allergies'][$meal] as $allergyItem => $mealTotal)
                                                            <div class="text-sm font-bold" wire:key="{{ $viewType . '-' . $viewDate . '-' . ($viewType == 'daily' ? $data['uuid'] : $data['ukey']) . '-' . $meal . '-' . $allergyItem }}">
                                                                {{ $allergyItem }} ({{ $mealTotal }}x)
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endif

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            {{ $data['group_name'] }}
                                        </td>

                                        @if ($viewType === 'daily')
                                            <td class="px-6 py-4 border-b border-gray-200 text-sm leading-5 text-gray-500">
                                                {{ $data['allergy'] ?: trans('vendors.no_allergy') }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endif

                            @if ($viewSetting != 'summary')
                                <tr class="border-t-3" wire:key="{{ $viewType . '-' . $viewDate . '-' . 'total' }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        &nbsp;
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        &nbsp;
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        &nbsp;
                                    </td>

                                    @if ($viewType == 'daily')
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-center text-gray-500">
                                            @foreach($mealTypes as $meal)
                                                <div wire:key="{{ $viewType . '-' . $viewDate . '-' . 'total-daily' . '-' . $meal }}" class="inline-flex text-base font-bold border-black leading-none w-8 justify-center {{ $loop->first ? '' : 'border-l' }}">
                                                    <span>{{ $totalMealValue[$meal] }}</span>
                                                </div>
                                            @endforeach
                                        </td>
                                    @else
                                        @foreach($mealTypes as $meal)
                                            <td wire:key="{{ $viewType . '-' . $viewDate . '-' . 'total-not-daily' . '-' . $meal }}" class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-lg font-bold text-center leading-5 text-gray-500">
                                                {{ $totalMealValue[$meal] }}
                                            </td>
                                        @endforeach
                                    @endif

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        &nbsp;
                                    </td>

                                    @if ($viewType == 'daily')
                                        <td class="px-6 py-4 border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            &nbsp;
                                        </td>
                                    @endif
                                </tr>
                            @endif
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if(! count($schedules))
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => 'data']) }}
        </x-no-data-found>
    @endif
</div>
