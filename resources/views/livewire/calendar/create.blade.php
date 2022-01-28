<div
    x-data
    x-on:create-new-calendar-event.window="@this.call('createNew')"
    x-on:edit-calendar-event.window="@this.call('editEvent', $event.detail)"
>
    <form wire:submit.prevent="submit">
        <x-jet-modal wire:model.defer="showModal">
            <x-slot name="title">
                @if($event->getKey())
                    {{ trans('calendar-events.update_modal_title') }}
                @else
                    {{ trans('calendar-events.add_modal_title') }}
                @endif
            </x-slot>

            <x-slot name="content">
                <div>
                    <label for="event.title" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('calendar-events.title_label') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.defer="event.title" id="event.title" name="event.title" type="text" required autocomplete="off" class="text-field @error('event.title') error @enderror" />
                    </div>

                    @error('event.title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="event.description" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('calendar-events.description_label') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <textarea wire:model.defer="event.description" id="event.description" name="event.description" row="7" class="text-field min-h-40 resize-none @error('event.description') error @enderror"></textarea>
                    </div>

                    @error('event.description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('informations.role') }}
                    </label>

                    <div class="mt-4 flex flex-wrap -mx-2">
                        @foreach(['Manager', 'Principal', 'Vendor', 'User'] as $role)
                            <div class="p-2 flex items-center sm:w-1/2">
                                <x-switch wire:model.defer="roles.{{ $role }}"></x-switch>

                                <label class="ml-4 text-sm font-medium text-gray-700 leading-5">
                                    {{ trans('extras.role_' . Str::lower($role)) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <label for="groups_id" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.group') }}
                    </label>
                    <p class="text-xs mb-2">{{ trans('calendar-events.groups_selection_label') }}</p>

                    @livewire('components.search-multi-select', [
                        'targetModel' => 'group',
                        'selected' => $groups_id,
                        'displayKey' => 'name',
                        'orderBy' => 'name',
                        'enableSearch' => false,
                        'wireKey' => 'users-create-groups',
                        'emitUpWhenUpdated' => [
                            'groups_id' => 'selected',
                        ],
                        'listenToEmit' => [
                            'calendar-events.create.selected-groups.updated',
                        ],
                        'extraLimitor' => [
                            'organization_id' => auth()->user()->organization_id,
                        ],
                    ])

                    @error('groups_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap -mx-2">
                    <div class="mt-4 px-2">
                        <label for="event.all_day" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('calendar-events.all_day_label') }}
                        </label>

                        <div class="mt-3.5">
                            <x-switch wire:model="event.all_day" />
                        </div>
                    </div>

                    <div class="px-2">
                        <div class="flex flex-wrap sm:flex-no-wrap -mx-2">
                            <div class="w-full px-2 mt-4 sm:max-w-48">
                                <label for="event.from" class="block text-sm font-medium text-gray-700 leading-5">
                                    {{ trans('calendar-events.from_label') }}
                                </label>

                                <div class="relative mt-1">
                                    <x-date-picker
                                        name="event.from"
                                        wire:model.defer="event.from"
                                        wire:notenabletime="event.all_day"
                                        class="@error('event.from') error @enderror"
                                    ></x-date-picker>
                                </div>

                                @error('event.from')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="w-full px-2 mt-4 sm:max-w-48">
                                <label for="event.to" class="block text-sm font-medium text-gray-700 leading-5">
                                    {{ trans('calendar-events.to_label') }}
                                </label>

                                <div class="relative mt-1">
                                    <x-date-picker
                                        name="event.to"
                                        wire:model.defer="event.to"
                                        wire:notenabletime="event.all_day"
                                        class="@error('event.to') error @enderror"
                                    ></x-date-picker>
                                </div>

                                @error('event.to')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 px-2">
                        <label for="event.color" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('calendar-events.color_label') }}
                        </label>

                        <div
                            class="flex pt-2 mt-1 -mx-0.5"
                                x-data="{
                                color: @entangle('event.color').defer,
                            }"
                        >
                            @foreach($this->themeColors as $color)
                                <div class="px-0.5" wire:key="{{ $color }}">
                                    <button
                                        x-on:click.prevent="color = '{{ $color }}'"
                                        class="w-6 h-6 rounded-md focus:outline-none hover:shadow-solid"
                                        x-bind:class="{
                                            'bg-gray-600': '{{ $color }}' == 'gray',
                                            'bg-red-600': '{{ $color }}' == 'red',
                                            'bg-yellow-600': '{{ $color }}' == 'yellow',
                                            'bg-green-600': '{{ $color }}' == 'green',
                                            'bg-blue-600': '{{ $color }}' == 'blue',
                                            'bg-indigo-600': '{{ $color }}' == 'indigo',
                                            'bg-purple-600': '{{ $color }}' == 'purple',
                                            'bg-pink-600': '{{ $color }}' == 'pink',
                                            'shadow-solid': '{{ $color }}' == color,
                                        }"
                                    ></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('calendar-events.attachment_label') }}
                    </label>

                    <div x-on:remove-file-from-event="@this.call('removeSingleFile', $event.detail)">
                        @if($isEditing)
                            <x-uploaded-files
                                :uuid="$event->uuid"
                                :uploaded-files="$this->getCurrentNotRemovedFiles()"
                            />
                        @endif
                    </div>

                    <x-file-attachment
                        mode="attachment"
                        :file="$files"
                        wire:model="files"
                        ext="pdfOrPhoto"
                        :multiple="true"
                    />

                    @error('event.file')
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
                        {{ trans('calendar-events.submit') }}
                    </button>

                    <button
                        type="button"
                        wire:click.prevent="cancel"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('calendar-events.cancel') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>
</div>
