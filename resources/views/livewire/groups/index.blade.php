<div
    x-on:delete-group.window="@this.call('deleteGroup', $event.detail)"
>
    <div class="mb-3">
        <div class="sm:flex justify-between">
            <x-search-box class="max-w-sm flex-1" :placeholder="$placeholder"></x-search-box>

            <div class="pt-4 sm:pt-0 text-right sm:pl-3 flex justify-between sm:inline-flex items-center">
                <div class="mr-4">
                    <x-pdf-download></x-pdf-download>
                </div>

                <a href="{{ route('groups.create') }}" class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                    <x-heroicon-o-plus class="-ml-0.5 mr-2 h-4 w-4" />
                    {{ trans('groups.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <x-table-td-sort
                                :text="trans('groups.name')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="name"
                                :filters-list="getColumnFilters('text')"
                                wire-key="group_name"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            @if (auth()->user()->isAdmin())
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    {{ trans('groups.organization') }}
                                </th>
                            @endif

                            <x-table-td-sort
                                :text="trans('groups.principals')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                justify-type="center"
                                name="principals_count"
                                :filters-list="getColumnFilters('number')"
                                wire-key="principals_count"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <x-table-td-sort
                                :text="trans('groups.users')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                justify-type="center"
                                name="users_count"
                                :filters-list="getColumnFilters('number')"
                                wire-key="users_count"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if ($groups->count())
                        <tbody class="bg-white">
                            @foreach($groups as $group)
                                <tr wire:key="{{ $group->uuid }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-indigo-500">
                                        <a href="{{ route('users.index', ['group' => $group->name]) }}" class="outline-none focus:outline-none">
                                            {{ $group->name }}
                                        </a>
                                    </td>

                                    @if (auth()->user()->isAdmin())
                                        <td class="px-6 py-4 border-b border-gray-200 text-sm leading-5 text-gray-500">
                                            @if ($group->organization)
                                                {{ $group->organization->name }} <br>
                                                {{ $group->organization->address }}
                                            @else
                                                {{ trans('groups.no_org') }}
                                            @endif
                                        </td>
                                    @endif

                                    <td class="px-6 py-4 text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        <div class="text-sm leading-5 text-gray-500">{{ $group->principals_count }}</div>
                                    </td>

                                    <td class="px-6 py-4 text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        <div class="text-sm leading-5 text-gray-500">{{ $group->users_count }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center">
                                            <a title="{{ trans('groups.edit') }}" href="{{ route('groups.edit', $group->uuid) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5"></x-heroicon-o-pencil>
                                            </a>

                                            <a title="{{ trans('groups.schedule') }}" href="{{ route('schedules.type.index', ['group', $group->uuid]) }}" class="ml-3 rounded-full text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-clock class="w-5 h-5"></x-heroicon-o-clock>
                                            </a>

                                            @if (auth()->user()->isAdminOrManager())
                                                <a
                                                    title="{{ trans('groups.delete_top_title') }}" href="#"
                                                    class="text-indigo-600 hover:text-indigo-900 ml-3"
                                                    x-on:click.prevent="$dispatch('group-delete-confirm-modal-open', {
                                                        'title': '{{ trans("groups.delete_top_title") }}',
                                                        'description': '{{ trans("groups.delete_description", ['title' => $group->name]) }}',
                                                        'event': 'delete-group',
                                                        'uuid': '{{ $group->uuid }}',
                                                    })"
                                                >
                                                    <x-heroicon-o-trash class="w-5 h-5"></x-heroicon-o-trash>
                                                </a>
                                            @endif
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

    @if(! $groups->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('groups.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $groups->links() }}
    </div>

    <x-confirm-modal confirm-id="group-delete"></x-confirm-modal>
</div>
