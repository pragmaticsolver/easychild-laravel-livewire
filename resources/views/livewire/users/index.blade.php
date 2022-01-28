<div
    x-on:delete-user.window="@this.call('deleteUser', $event.detail)"
>
    <div class="mb-3">
        <div class="sm:flex justify-between">
            <x-search-box class="max-w-sm flex-1" :placeholder="$placeholder"></x-search-box>

            <div class="pt-4 sm:pt-0 text-right sm:pl-3 flex justify-between sm:inline-flex items-center">
                <div class="mr-4">
                    <x-pdf-download></x-pdf-download>
                </div>

                <a
                    @if (auth()->user()->isAdminOrManager())
                        href="{{ route('users.create') }}"
                    @else
                        href="#"
                        x-data
                        x-on:click.prevent
                        :class="{'pointer-events-none opacity-50 cursor-default': true}"
                    @endif
                    class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                >
                    <x-heroicon-o-plus class="-ml-0.5 mr-2 h-4 w-4" />
                    {{ trans('users.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 overflow-x-auto">
            <div class="align-middle inline-block overflow-hidden min-w-full shadow sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <x-table-td-sort
                                :text="trans('users.name')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="given_names"
                                :filters-list="getColumnFilters('text')"
                                wire-key="user_given_names"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <th
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                <x-sort-column
                                    :text="trans('users.created')"
                                    :sort-by="$sortBy"
                                    name="created_at"
                                    :order="$sortOrder"
                                ></x-sort-column>
                            </th>

                            <x-table-td-sort
                                :text="trans('users.organization')"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="organization_name"
                                :filters-list="getColumnFilters('text')"
                                wire-key="organization_name_add_group"
                                :drop-to-left="true"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                                :visible="auth()->user()->isAdminOrManager()"
                                :sort-enabled="auth()->user()->isAdmin()"
                            ></x-table-td-sort>

                            <x-table-td-sort
                                :text="trans('users.role')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="role"
                                :filters-list="getColumnFilters('role')"
                                wire-key="user_role"
                                :drop-to-left="true"
                                :is-boolean-type="true"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <x-table-td-sort
                                :text="trans('users.photo_permission')"
                                :sort-enabled="true"
                                :sort-by="$sortBy"
                                :order="$sortOrder"
                                name="photo_permission"
                                :filters-list="getColumnFilters('photo_permission')"
                                wire-key="photo_permission"
                                :drop-to-left="true"
                                :is-boolean-type="true"
                                class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500"
                            ></x-table-td-sort>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($users->count())
                        <tbody class="bg-white">
                            @foreach($users as $user)
                                <tr wire:key="{{ $user->uuid }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <div class="text-sm leading-5 font-medium text-gray-900">
                                            <a class="text-indigo-500" href="{{ route('users.edit', ['user' => $user]) }}">
                                                ({{$user->id}}) {{ $user->full_name }}
                                            </a>
                                        </div>

                                        @if($user->email)
                                            <div class="text-sm leading-5 text-gray-500">{{ $user->email }}</div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <div class="text-sm leading-5 text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                                    </td>

                                    <td class="px-6 py-4 border-b border-gray-200 text-sm leading-5 text-gray-500 max-w-sm">
                                        @if ($user->organization)
                                            {{ $user->organization->name }} <br>
                                            <strong>{{ trans('organizations.address') }}:</strong> {{ $user->organization->address }}

                                            @if (count($user->groups))
                                                <br>
                                                <strong>{{ trans('groups.title') }}:</strong> {{ $user->groups->pluck('name')->join(', ') }}
                                            @endif
                                        @else
                                            {{ trans('users.no_org') }}
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ trans('extras.role_' . Str::of($user->role)->lower()) }}
                                    </td>

                                    <td class="px-6 py-4 border-b border-gray-200 text-center">
                                        @if (Str::of($user->role)->lower() == 'user')
                                            @livewire('users.set-available', compact('user'), key($user['uuid']))
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center">
                                            <a title="{{ trans('users.edit') }}" href="{{ route('users.edit', $user->uuid) }}"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5"></x-heroicon-o-pencil>
                                            </a>

                                            @if (auth()->user()->canImpersonate() && $user->canBeImpersonated())
                                                <a
                                                    href="#"
                                                    title="{{ trans('impersonations.user_impersonate') }}"
                                                    class="text-indigo-600 hover:text-indigo-900 ml-3"
                                                    x-on:click.prevent="$dispatch('impersonation-impersonate-user', '{{ $user->uuid }}')"
                                                >
                                                    <x-heroicon-o-finger-print class="w-5 h-5"></x-heroicon-o-finger-print>
                                                </a>
                                            @endif

                                            @if (! auth()->user()->isPrincipal())
                                                <a
                                                    title="{{ trans('users.delete') }}" href="#"
                                                    class="text-indigo-600 hover:text-indigo-900 ml-3"
                                                    x-on:click.prevent="$dispatch('user-delete-confirm-modal-open', {
                                                        'title': '{{ trans("users.delete_confirm_title") }}',
                                                        'description': '{{ trans("users.delete_confirm_description") }}',
                                                        'event': 'delete-user',
                                                        'uuid': '{{ $user->uuid }}',
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

    @if(! $users->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('users.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $users->links() }}
    </div>

    <x-confirm-modal confirm-id="user-delete"></x-confirm-modal>
</div>
