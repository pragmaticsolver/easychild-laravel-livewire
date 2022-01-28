<div
    class="sm:mx-auto sm:w-full sm:max-w-4xl"
    x-on:user-parent-unlink.window="@this.call('unlinkParent', $event.detail)"
>
    <div class="flex items-center justify-between pb-4">
        <h1 class="text-lg leading-6 font-semibold text-gray-900 mb-2">
            {{ trans('users.sub-nav.parents') }}
        </h1>

        <button
            type="button"
            wire:click.prevent="createNew"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
            {{ trans('parents.add_new_btn') }}
        </button>
    </div>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 overflow-x-auto">
            <div class="align-middle inline-block overflow-hidden min-w-full shadow sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('parents.email') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('parents.linked_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('parents.linked_at_title') }}
                            </th>

                            <th width="120" class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($parents->count())
                        <tbody class="bg-white">
                            @foreach($parents as $parent)
                                <tr class="align-top" wire:key="parent-user-{{ $parent->id }}">
                                    <td class="px-6 py-4 border-b border-gray-200">
                                        {{ $parent->email }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        @if ($parent->linked)
                                            <span title="{{ trans('parents.linked') }}">
                                                <x-heroicon-o-badge-check class="w-5 h-5 text-indigo-600" />
                                            </span>
                                        @else
                                            <span title="{{ trans('parents.not_linked') }}">
                                                <x-heroicon-o-x-circle class="w-5 h-5 text-red-600" />
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        @if ($parent->linked)
                                            {{ $parent->updated_at->format(config('setting.format.date')) }}
                                        @else
                                            {{ trans('parents.not_linked') }}
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center justify-end">
                                            @if (! $parent->linked)
                                                <button
                                                    title="{{ trans('parents.resend_link_email') }}"
                                                    class="text-indigo-600 hover:text-indigo-900 focus:outline-none outline-none"
                                                    wire:click.prevent="resendLinkEmail({{ $parent->id }})"
                                                >
                                                    <x-heroicon-o-inbox class="w-5 h-5" />
                                                </button>
                                            @endif

                                            @if (auth()->user()->isManager())
                                                <button
                                                    title="{{ trans('parents.unlink') }}"
                                                    class="text-indigo-600 hover:text-indigo-900 ml-3 focus:outline-none outline-none"
                                                    x-on:click.prevent="$dispatch('user-parent-unlink-confirm-modal-open', {
                                                        'title': '{{ trans("parents.unlink_confirm_title") }}',
                                                        'description': '{{ trans("parents.unlink_confirm_description") }}',
                                                        'event': 'user-parent-unlink',
                                                        'uuid': '{{ $parent->id }}',
                                                    })"
                                                >
                                                    <x-heroicon-o-trash class="w-5 h-5" />
                                                </button>
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

    @if(! $parents->count())
        <x-no-data-found>
            {{ trans('parents.no_items') }}
        </x-no-data-found>
    @endif

    <form wire:submit.prevent="submit">
        <x-jet-modal wire:model.defer="showModal" max-width="sm">
            <x-slot name="title">{{ trans('parents.add_modal_title') }}</x-slot>

            <x-slot name="content">
                <div class="mt-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.email') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.defer="email" id="email" name="email" type="email" required autocomplete="off" placeholder="{{ trans('parents.email_placeholder') }}" class="text-field @error('email') error @enderror" />
                    </div>

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-slot>

            <x-slot name="footer">
                <div class="flex space-x-4">
                    <button
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('parents.add_new_submit') }}
                    </button>

                    <button
                        type="button"
                        wire:click.prevent="$set('showModal', false)"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('parents.add_new_cancel') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>

    <x-confirm-modal confirm-id="user-parent-unlink"></x-confirm-modal>
</div>
