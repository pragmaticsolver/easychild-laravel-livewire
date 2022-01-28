<div
    class="sm:mx-auto sm:w-full sm:max-w-4xl"
    x-on:delete-user-contact.window="@this.call('deleteContact', $event.detail)"
>
    <div class="flex items-center justify-between pb-4">
        <h1 class="text-lg leading-6 font-semibold text-gray-900 mb-2">
            {{ trans('users.sub-nav.contacts') }}
        </h1>

        <button
            type="button"
            wire:click.prevent="createNew"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
            {{ trans('contacts.add_btn') }}
        </button>
    </div>

    <ul class="flex flex-wrap -mx-2">
        @forelse($contacts as $item)
            <li class="px-2 py-4 w-full md:w-1/2" wire:key="{{ $item->id }}">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-gray-400 rounded-lg px-4 py-2 flex items-center">
                        <div class="mr-4 flex-shrink-0 w-8">
                            <img src="{{ $item->avatar_url }}" alt="{{ $item->name }}" class="w-8 h-8 rounded-full">
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center justify-between space-x-3 mb-2">
                                <h3 class="text-black text-sm mr-2 font-medium truncate">{{ $item->name }}</h3>

                                @if($item->legal)
                                    <span class="flex-shrink-0 inline-block px-2 py-0.5 text-white text-xs font-medium bg-blue-600 rounded-full">{{ trans('contacts.legal') }}</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between">
                                <p class="text-black text-sm truncate mr-4">{{ $item->address }}</p>

                                <a
                                    title="{{ trans('contacts.delete_top_title') }}" href="#"
                                    class="text-indigo-600 hover:text-indigo-900 ml-3"
                                    x-on:click.prevent="$dispatch('user-contact-delete-confirm-modal-open', {
                                        'title': '{{ trans("contacts.delete_top_title") }}',
                                        'description': '{{ trans("contacts.delete_description") }}',
                                        'event': 'delete-user-contact',
                                        'uuid': '{{ $item->id }}',
                                    })"
                                >
                                    <x-heroicon-o-trash class="w-5 h-5"></x-heroicon-o-trash>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-2 flex flex-wrap">
                        @php
                            $booleans = ['emergency_contact', 'can_collect'];
                            $fields = ['emergency_contact', 'can_collect', 'relationship', 'landline', 'mobile', 'job', 'notes'];
                        @endphp

                        @foreach($fields as $field)
                            <div class="mb-1 w-1/2 px-2" wire:key="user-{{ $item->id }}-contact-field-{{ $field }}">
                                <h4 class="font-semibold">{{ trans("contacts.{$field}") }}</h4>
                                <p class="text-sm">
                                    @if (in_array($field, $booleans))
                                        {{ $item->$field ? trans('extras.yes') : trans('extras.no') }}
                                    @else
                                        {{ $item->$field ?? '-' }}
                                    @endif
                                </p>
                            </div>
                        @endforeach

                        <div class="mb-1 w-1/2 px-2 pt-2">
                            <button
                                type="button"
                                wire:click.prevent="edit({{ $item->id }})"
                                class="inline-flex justify-center px-2 py-1 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ trans('contacts.edit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="px-2 py-4 w-full">
                {{ trans('contacts.no_items') }}
            </li>
        @endforelse
    </ul>

    <form wire:submit.prevent="submit">
        <x-jet-modal wire:model.defer="showModal">
            <x-slot name="title">{{ trans('contacts.add_modal_title') }}</x-slot>

            <x-slot name="content">
                <div class="flex flex-wrap -mx-2">
                    <div class="w-full sm:w-1/2 px-2">
                        <label for="contact.name" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.name') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contact.name" id="contact.name" name="contact.name" type="text" required autocomplete="off" class="text-field @error('contact.name') error @enderror" />
                        </div>

                        @error('contact.name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full sm:w-1/2 px-2 mt-4 sm:mt-0">
                        <label for="contact.relationship" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.relationship') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contact.relationship" id="contact.relationship" name="contact.relationship" type="text" autocomplete="off" class="text-field @error('contact.relationship') error @enderror" />
                        </div>

                        @error('contact.relationship')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap -mx-2">
                    <div class="w-full sm:w-1/2 px-2">
                        <label for="contact.address" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.address') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contact.address" id="contact.address" name="contact.address" type="text" autocomplete="off" class="text-field @error('contact.address') error @enderror" />
                        </div>

                        @error('contact.address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full sm:w-1/2 px-2 mt-4 sm:mt-0">
                        <label for="contact.landline" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.landline') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contact.landline" id="contact.landline" name="contact.landline" type="text" autocomplete="off" class="text-field @error('contact.landline') error @enderror" />
                        </div>

                        @error('contact.landline')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap -mx-2">
                    <div class="w-full sm:w-1/2 px-2">
                        <label for="contact.mobile" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.mobile') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contact.mobile" id="contact.mobile" name="contact.mobile" type="text" autocomplete="off" class="text-field @error('contact.mobile') error @enderror" />
                        </div>

                        @error('contact.mobile')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="w-full sm:w-1/2 px-2 mt-4 sm:mt-0">
                        <label for="contact.job" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.job') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="contact.job" id="contact.job" name="contact.job" type="text" autocomplete="off" class="text-field @error('contact.job') error @enderror" />
                        </div>

                        @error('contact.job')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="contact.notes" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('contacts.notes') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <textarea wire:model.defer="contact.notes" id="contact.notes" name="contact.notes" row="7" class="text-field resize-none @error('contact.notes') error @enderror"></textarea>
                    </div>

                    @error('contact.notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.avatar_title') }}
                    </label>

                    @if ($showModal)
                        <x-avatar-upload :default="$newAvatar ?: $avatar" wire:model.defer="newAvatar"></x-avatar-upload>
                    @endif
                </div>

                <div class="pt-3 flex flex-wrap -mx-2">
                    <div class="w-full sm:w-1/2 py-3 px-2 flex items-center">
                        <x-switch wire:model.defer="contact.legal"></x-switch>

                        <label for="contact.legal" class="ml-4 text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.legal') }}
                        </label>
                    </div>

                    <div class="w-full sm:w-1/2 py-3 px-2 flex items-center">
                        <x-switch wire:model.defer="contact.can_collect"></x-switch>

                        <label for="contact.can_collect" class="ml-4 text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.can_collect') }}
                        </label>
                    </div>

                    <div class="w-full sm:w-1/2 py-3 px-2 flex items-center">
                        <x-switch wire:model.defer="contact.emergency_contact"></x-switch>

                        <label for="contact.emergency_contact" class="ml-4 text-sm font-medium text-gray-700 leading-5">
                            {{ trans('contacts.emergency_contact') }}
                        </label>
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
                        {{ trans('contacts.submit') }}
                    </button>

                    <button
                        type="button"
                        wire:click.prevent="$set('showModal', false)"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('contacts.cancel') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>

    <x-confirm-modal confirm-id="user-contact-delete"></x-confirm-modal>
</div>
