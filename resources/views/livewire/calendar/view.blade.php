<div x-on:view-calendar-event.window="@this.call('open', $event.detail)">
    <x-jet-modal wire:model.defer="showModal">
        <x-slot name="title">
            @if ($event->birthday)
                {{ $event->birthdayUser->full_name }} ({{ trans('calendar-events.birthday_description') }})
            @else
                {{ $event->title }}
            @endif
        </x-slot>

        <x-slot name="content">
            @if (! $event->birthday)
                <div class="mt-4">
                    <label for="event.description" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('calendar-events.description_label') }}
                    </label>

                    <div class="">
                        {!! nl2br($event->description) !!}
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <label for="event.description" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('calendar-events.groups_label') }}
                </label>

                <div class="">
                    @if($this->groups && count($this->groups))
                        {{ join(', ', $this->groups) }}
                    @else
                        {{ trans('calendar-events.no_group_selected') }}
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap -mx-2">
                <div class="mt-4 px-2">
                    <label for="event.all_day" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('calendar-events.all_day_label') }}
                    </label>

                    <div class="mt-1">
                        @if ($event->all_day)
                            <x-heroicon-o-check class="w-6 h-6"></x-heroicon-o-check>
                        @else
                            <x-heroicon-o-x class="w-6 h-6"></x-heroicon-o-x>
                        @endif
                    </div>
                </div>

                <div class="mt-4 px-2">
                    <label for="event.color" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('calendar-events.color_label') }}
                    </label>

                    <div class="mt-1">
                        <div
                            class="w-6 h-6 rounded-md focus:outline-none hover:shadow-solid"
                            x-data="{
                                'color': '{{ $event->color }}'
                            }"
                            x-bind:class="{
                                'bg-gray-600': color == 'gray',
                                'bg-red-600': color == 'red',
                                'bg-yellow-600': color == 'yellow',
                                'bg-green-600': color == 'green',
                                'bg-blue-600': color == 'blue',
                                'bg-indigo-600': color == 'indigo',
                                'bg-purple-600': color == 'purple',
                                'bg-pink-600': color == 'pink',
                            }"
                        ></div>
                    </div>
                </div>
            </div>

            @if ($event->getKey())
                <div class="flex flex-wrap sm:flex-no-wrap -mx-2">
                    <div class="w-full px-2 mt-4 sm:max-w-48">
                        <label for="event.from" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('calendar-events.from_label') }}
                        </label>

                        <div>
                            {{ $event->from->format($this->dateFormat) }}
                        </div>
                    </div>

                    <div class="w-full px-2 mt-4 sm:max-w-48">
                        <label for="event.to" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('calendar-events.to_label') }}
                        </label>

                        <div>
                            {{ $event->to->format($this->dateFormat) }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('calendar-events.attachment_label') }}
                </label>

                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-0 sm:gap-1 md:gap-2">
                    @forelse ($event->files as $file)
                        <a
                            href="#"
                            wire:click.prevent="$emitTo('components.file-modal', 'file-model-open', {{ json_encode($file) }})"
                            class="flex shadow-md rounded-md cursor-pointer my-1.5"
                        >
                            <div class="flex-shrink-0 flex item-center justify-center w-10 bg-indigo-400 text-white rounded-l-md py-2">
                                @if($file['type'] == 'image')
                                    <x-heroicon-o-photograph class="w-6 h-6"></x-heroicon-o-photograph>
                                @else
                                    <x-heroicon-o-document-text class="w-6 h-6"></x-heroicon-o-document-text>
                                @endif
                            </div>

                            <div class="flex items-center flex-1 rounded-r-md py-1 border-t border-b border-r border-gray-600 truncate">
                                <div class="text-gray-800 text-sm px-3 truncate">
                                    {{ $file['name'] }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="flex shadow-md rounded-md">
                            <div class="flex-shrink-0 flex item-center justify-center w-10 bg-indigo-400 text-white rounded-l-md py-2">
                                <x-heroicon-o-ban class="w-6 h-6"></x-heroicon-o-ban>
                            </div>

                            <div class="flex items-center flex-1 rounded-r-md py-1 border-t border-b border-r border-gray-600 truncate">
                                <div class="text-gray-800 text-sm px-3 truncate">
                                    {{ trans('calendar-events.no_files_uploded') }}
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="flex space-x-4">
                <button
                    type="button"
                    wire:click.prevent="close"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    {{ trans('components.close') }}
                </button>
            </div>
        </x-slot>
    </x-jet-modal>

    @livewire('components.file-modal')
</div>
