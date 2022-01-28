<div class="p-4">
    <div class="bg-white shadow-md border rounded-md">
        <div class="flex flex-col">
            <div class="overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                <div class="align-middle inline-block min-w-full overflow-hidden">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                        <button wire:click.prevent="prevYear" type="button" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            <span class="sr-only">Previous</span>

                                            <x-heroicon-o-chevron-left class="h-5 w-5" />
                                        </button>

                                        <div class="text-center w-16 px-4 border bg-white border-gray-300 py-2 text-gray-500 text-sm font-medium">
                                            {{ $year->format('Y') }}
                                        </div>

                                        <button wire:click.prevent="nextYear" type="button" class="-ml-px relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            <span class="sr-only">Next</span>

                                            <x-heroicon-o-chevron-right class="h-5 w-5" />
                                        </button>
                                    </span>
                                </th>

                                <x-table-td-sort
                                    :text="trans('reports.manager_reports.sick_days')"
                                    :sort-enabled="true"
                                    :sort-by="$sortBy"
                                    :order="$sortOrder"
                                    name="sick_days"
                                    :filters-list="getColumnFilters('number')"
                                    wire-key="sick_days"
                                    class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500 max-w-20"
                                ></x-table-td-sort>

                                <x-table-td-sort
                                    :text="trans('reports.manager_reports.cure_days')"
                                    :sort-enabled="true"
                                    :sort-by="$sortBy"
                                    :order="$sortOrder"
                                    name="cure_days"
                                    :filters-list="getColumnFilters('number')"
                                    wire-key="cure_days"
                                    class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500 max-w-20"
                                ></x-table-td-sort>

                                <x-table-td-sort
                                    :text="trans('reports.manager_reports.holiday_days')"
                                    :sort-enabled="true"
                                    :sort-by="$sortBy"
                                    :order="$sortOrder"
                                    name="holiday_days"
                                    :filters-list="getColumnFilters('number')"
                                    wire-key="holiday_days"
                                    class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500 max-w-20"
                                ></x-table-td-sort>

                                <x-table-td-sort
                                    :text="trans('reports.manager_reports.other_days')"
                                    :sort-enabled="true"
                                    :sort-by="$sortBy"
                                    :order="$sortOrder"
                                    name="other_days"
                                    :filters-list="getColumnFilters('number')"
                                    wire-key="other_days"
                                    class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500 max-w-20"
                                ></x-table-td-sort>

                                <x-table-td-sort
                                    :text="trans('reports.manager_reports.holiday_days_continuous')"
                                    :sort-enabled="true"
                                    :sort-by="$sortBy"
                                    :order="$sortOrder"
                                    name="holiday_days_continuous"
                                    :filters-list="getColumnFilters('number')"
                                    wire-key="holiday_days_continuous"
                                    class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500 max-w-32"
                                ></x-table-td-sort>
                            </tr>
                        </thead>

                        @if ($users->count())
                            <tbody>
                                @foreach($users as $user)
                                    <tr wire:key="{{ $user->uuid }}">
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500 max-w-56">
                                            <div class="text-sm flex justify-start items-center">
                                                <div class="flex-1 max-w-56">
                                                    <div class="truncate">
                                                        <a class="text-indigo-500" href="{{ route('users.edit', ['user' => $user->uuid]) }}">
                                                            {{ $user->full_name }} ({{ $user->id }})
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="flex-shrink-0 w-16 px-4">
                                                    @if ($user->avatar_url)
                                                        <img class="inline-block h-6 w-6 rounded-full" src="{{ $user->avatar_url }}" alt="">
                                                    @else
                                                        <svg class="h-6 w-6 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                        </svg>
                                                    @endif
                                                </div>

                                                <div class="flex-shrink-0 w-28 mr-2 whitespace-no-wrap truncate">
                                                    @if (auth()->user()->isManager() && $user->group_uuid)
                                                        <a class="text-indigo-500" href="{{ route('groups.edit', $user->group_uuid) }}">
                                                            {{ $user->group_name }}
                                                        </a>
                                                    @else
                                                        {{ $user->group_name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500">
                                            {{ $user->sick_days }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500">
                                            {{ $user->cure_days }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500">
                                            {{ $user->holiday_days }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500">
                                            {{ $user->other_days }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500">
                                            {{ $user->holiday_days_continuous ?? '0' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @if(! $users->count())
            <x-no-data-found>
                {{ trans('pagination.not_found', ['type' => trans('users.title_lower')]) }}
            </x-no-data-found>
        @endif

        @if ($users->hasPages())
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
