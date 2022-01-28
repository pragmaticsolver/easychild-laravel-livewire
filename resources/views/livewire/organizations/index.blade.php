<div>
    <div class="mb-3">
        <div class="sm:flex justify-between">
            <x-search-box class="max-w-sm flex-1" :placeholder="$placeholder"></x-search-box>

            <div class="pt-4 sm:pt-0 text-right sm:pl-3 flex justify-between sm:inline-flex items-center">
                <div class="mr-4">
                    <x-pdf-download></x-pdf-download>
                </div>

                <a href="{{ route('organizations.create') }}" class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
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
                                :text="trans('organizations.name')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="name"
                                :filters-list="getColumnFilters('text')"
                                wire-key="organization_name"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <x-table-td-sort
                                :text="trans('organizations.address')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="address"
                                :filters-list="getColumnFilters('text')"
                                wire-key="organization_address"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <x-table-td-sort
                                :text="trans('organizations.groups')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="groups_count"
                                justify-type="center"
                                :filters-list="getColumnFilters('number')"
                                wire-key="organization_groups"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <x-table-td-sort
                                :text="trans('organizations.users')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="users_count"
                                justify-type="center"
                                :filters-list="getColumnFilters('number')"
                                wire-key="organization_users"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($organizations->count())
                        <tbody class="bg-white">
                            @foreach($organizations as $organization)
                                <tr wire:key="{{ $organization->uuid }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <div class="text-sm leading-5 text-gray-500">{{ $organization->name }}</div>
                                    </td>

                                    <td class="px-6 py-4 border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ $organization->address }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm text-center leading-5 text-gray-500">
                                        {{ $organization->groups_count }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm text-center leading-5 text-gray-500">
                                        {{ $organization->users_count }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center">
                                            <a title="{{ trans('organizations.edit') }}" href="{{ route('organizations.edit', $organization->uuid) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5"></x-heroicon-o-pencil>
                                            </a>

                                            <a title="{{ trans('organizations.schedule') }}" href="{{ route('schedules.type.index', ['organization', $organization->uuid]) }}"
                                                class="ml-3 rounded-full text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-clock class="w-5 h-5"></x-heroicon-o-clock>
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

    @if(! $organizations->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('organizations.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $organizations->links() }}
    </div>
</div>
