<div
    x-on:delete-event.window="@this.call('deleteEvent', $event.detail)"
>
    <header class="bg-white shadow-sm">
        <div class="flex flex-wrap items-center max-w-7xl mx-auto px-4 py-2">
            <div class="mr-3 flex-1">
                <h1 class="text-lg leading-6 font-semibold text-gray-900">
                    {{ $this->pageTitle }}
                </h1>

                <div class="text-sm leading-5">{{ $this->currentWeek }}</div>
            </div>

            <div class="flex space-x-2">
                {{-- <a
                    href="{{ $iCalRoute }}"
                    target="_blank"
                    class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                >
                    <x-heroicon-o-calendar class="h-4 w-4" />
                </a> --}}

                <button
                    wire:click.prevent="previous"
                    class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                >
                    <x-heroicon-o-chevron-left class="sm:-ml-0.5 sm:mr-2 h-4 w-4" />
                    <span class="hidden sm:block">
                        {{ trans('calendar-events.previous_week') }}
                    </span>
                </button>

                <button
                    wire:click.prevent="next"
                    class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                >
                    <span class="hidden sm:block">
                        {{ trans('calendar-events.next_week') }}
                    </span>
                    <x-heroicon-o-chevron-right class="sm:-mr-0.5 sm:ml-2 h-4 w-4" />
                </button>

                @if (auth()->user()->isManagerOrPrincipal())
                    <button
                        x-data
                        x-on:click.prevent="$dispatch('create-new-calendar-event')"
                        class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
                    >
                        <x-heroicon-o-plus class="-ml-0.5 mr-2 h-4 w-4" />
                        {{ trans('calendar-events.add_new') }}
                    </button>
                @endif
            </div>
        </div>
    </header>

    <div class="border-b flex flex-wrap sm:flex-no-wrap sm:overflow-x-auto sm:h-calendarscreen custom-scrollbar">
        @foreach($eventItems as $eventItem)
            <div class="border-l border-r flex-shrink-0 w-full sm:flex sm:flex-col sm:w-96 sm:max-w-xs {{ $loop->first ? ' ml-auto' : '' }} {{ $loop->last ? ' mr-auto' : '' }}" wire:key="{{ $eventItem['date']->format('Y-m-d') }}">
                <div class="p-3 font-medium whitespace-no-wrap">
                    {{ trans('calendar-events.main_card_week_day_title', [
                        'day' => $eventItem['date']->dayName,
                        'date' => $eventItem['date']->format('d'),
                        'month' => $eventItem['date']->format('m'),
                    ]) }}
                </div>

                <div class="px-1.5 space-y-3 flex-1 sm:overflow-y-auto custom-scrollbar">
                    @if ($eventItem && Arr::has($eventItem, 'events') && $eventItem['events'])
                        @foreach($eventItem['events'] as $event)
                            <div>
                                <div
                                    class="bg-white shadow-md border border-gray-300 rounded-md pl-4 pr-1.5 pt-2 pb-3 relative cursor-pointer"
                                    wire:key="{{ $eventItem['date']->format('Y-m-d') }}-{{ $event->uuid }}"
                                    x-on:click.prevent="$dispatch('view-calendar-event', '{{ $event->uuid }}')"
                                >
                                    <div
                                        class="absolute top-2 left-1 bottom-2 w-1.5 rounded-full"
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

                                    <div class="flex items-center {{ ($event->childAvatars || $event->birthdayUser) ? ' mb-2' : '' }}">
                                        @if ($event->birthdayUser)
                                            <div class="text-sm mb-0.5 truncate">{{ $event->birthdayUser->full_name }}</div>
                                        @else
                                            <div class="text-sm mb-0.5 truncate">{{ $event->title }}</div>
                                        @endif

                                        @if ($event->childAvatars)
                                            <div class="ml-2 flex-1 flex">
                                                @foreach($event->childAvatars as $childAvatar)
                                                    <div class="flex-shrink-0 px-1">
                                                        <img class="w-7 h-7 rounded-full" src="{{ $childAvatar }}" alt="">
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($event->birthdayUser)
                                            <div class="ml-2 flex-1 flex">
                                                <div class="flex-shrink-0 px-1">
                                                    <img class="w-7 h-7 rounded-full" src="{{ $event->birthdayUser->avatar_url }}" alt="">
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($event->groups && count($event->groups))
                                        <div class="flex flex-wrap -mx-1">
                                            @foreach($event->groups as $evtGroup)
                                                @php
                                                    $currentGroup = $groups->where('id', $evtGroup)->first()
                                                @endphp

                                                @if ($currentGroup)
                                                    <span class="inline-flex items-center px-2 rounded-md text-xs bg-indigo-400 mx-1 my-0.5 text-white">
                                                        {{ $currentGroup->name }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($event->birthdayUser)
                                        <div class="text-xs line-clamp-2 mt-2">{{ trans('calendar-events.birthday_description') }}</div>
                                    @else
                                        <div class="text-xs line-clamp-2 mt-2">{{ $event->description }}</div>
                                    @endif
                                </div>

                                @php
                                    $canDeleteEvent = auth()->user()->isPrincipal() && $event->creator_id == auth()->id();
                                    $canEditOrDelete = $canDeleteEvent || auth()->user()->isManager();

                                    if ($event->birthday) {
                                        $canEditOrDelete = false;
                                    }
                                @endphp

                                @if ($canEditOrDelete)
                                    <div class="flex justify-end">
                                        <a
                                            title="{{ trans('calendar-events.tooltips.delete') }}"
                                            href="#"
                                            class="py-1 px-2 bg-gray-200 rounded-b-md inline-flex text-indigo-600 hover:text-indigo-900"
                                            x-on:click.prevent="$dispatch('event-delete-confirm-modal-open', {
                                                'title': '{{ trans("calendar-events.delete_confirm_title") }}',
                                                'description': '{{ trans("calendar-events.delete_confirm_description") }}',
                                                'event': 'delete-event',
                                                'uuid': '{{ $event->uuid }}',
                                            })"
                                        >
                                            <x-heroicon-o-trash class="w-5 h-5"></x-heroicon-o-trash>
                                        </a>

                                        <a
                                            title="{{ trans('calendar-events.tooltips.edit') }}"
                                            href="#"
                                            class="py-1 px-2 bg-gray-200 rounded-b-md ml-4 inline-flex text-indigo-600 hover:text-indigo-900"
                                            x-on:click.prevent="$dispatch('edit-calendar-event', '{{ $event->uuid }}')"
                                        >
                                            <x-heroicon-o-pencil class="w-5 h-5"></x-heroicon-o-pencil>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    <div class="h-4"></div>
                </div>
            </div>
        @endforeach
    </div>

    @livewire('calendar.view')

    @if (auth()->user()->isManagerOrPrincipal())
        @livewire('calendar.create')

        <x-confirm-modal confirm-id="event-delete"></x-confirm-modal>
    @endif
</div>
