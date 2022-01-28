<div
    class="fixed z-10 sm:z-0 inset-0 flex items-center justify-center sm:static sm:flex-shrink-0 sm:w-64 sm:items-stretch sm:flex-imp sm:visible-imp sm:transition-none"
    x-show="form.enabled"
    x-data="{
        form: {
            enabled: false,
        }
    }"
    :class="{'invisible': !form.enabled}"
    x-cloak
    x-on:chat-threads-enable.window="form.enabled = true"
    x-init="
        form.enabled = window.innerWidth >= 640;
        window.livewire.emit('userChangedTheThread', {{ $loadedActiveThread }})
    "
>
    <div
        x-show="form.enabled"
        class="sm:hidden fixed inset-0 transition-opacity sm:transition-none"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.prevent="form.enabled = false"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="form.enabled"
        class="max-h-mscreen relative custom-scrollbar overflow-y-auto bg-white rounded-lg p-4 m-auto my-4 shadow-xl transition-all max-w-md w-full sm:p-0 sm:my-0 sm:shadow-none sm:transition-none sm:flex-imp sm:opacity-100"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="w-full">
            <h3 class="sm:hidden text-sm leading-5 font-medium text-gray-700 mb-3 flex items-center justify-between" id="modal-headline">
                <span class="pr-3">{{ trans('messages.sidebar.title') }}</span>

                <button x-on:click.prevent="form.enabled = false" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </h3>

            {{-- Left sidebar --}}
            <aside class="sm:w-full">
                <a href="#" x-on:click.prevent="$dispatch('show-create-new-room-modal')" class="px-2 py-2 block no-underline hover:bg-gray-100 border-b border-gray-200 transition duration-150 ease-in-out cursor-pointer">
                    <p class="text-sm leading-5 font-medium text-gray-700">
                        {{ trans('messages.sidebar.create_new') }}
                    </p>
                </a>

                <div class="relative p-2 bg-gray-200 rounded-t-md">
                    <input type="text" wire:model.debounce.500ms="search" class="text-field" placeholder="{{ trans('extras.search') }}">

                    <div wire:loading.class.remove="opacity-0" class="opacity-0 absolute inset-y-0 right-0 pr-3 leading-none flex items-center pointer-events-none">
                        <x-loading></x-loading>
                    </div>
                </div>

                @foreach($threads as $thread)
                    <div
                        class="px-2 py-2 transition duration-150 ease-in-out cursor-pointer border-b border-gray-200 {{ $activeThread === $thread->id ? 'bg-indigo-400 text-white' : 'hover:bg-gray-100' }}"
                        {{-- wire:click="setActiveThread({{ $thread->id }})" --}}
                        x-on:click.prevent="@this.call('setActiveThread', {{ $thread->id }});form.enabled = false;"
                        wire:key="conversation-thread-{{ $thread->id }}"
                    >
                        <p class="text-sm leading-5 font-medium">
                            @if ($thread->chat_type == 'custom')
                                {{ $thread->title }} &bull; <small class="align-top">&#128274;</small>
                            @elseif($thread->private)
                                @if (($thread->creator_id == auth()->user()->id) || auth()->user()->isParent())
                                    @if (Str::startsWith($thread->title, 'messages.roles.'))
                                        {{ trans($thread->title) }} &bull; <small class="align-top">&#128274;</small>
                                    @else
                                        {{ $thread->title }} &bull; <small class="align-top">&#128274;</small>
                                    @endif
                                @else
                                    {{ $thread->alt_title }} &bull; <small class="align-top">&#128274;</small>
                                @endif
                            @else
                                {{ $thread->title }} &bull;
                                @if($thread->chat_type == 'users')
                                    {{ trans("messages.roles.users") }}
                                @elseif($thread->chat_type == 'principals')
                                    {{ trans("messages.roles.principals") }}
                                @elseif($thread->chat_type == 'admins')
                                    {{ trans("messages.roles.admins") }}
                                @elseif($thread->chat_type == 'managers')
                                    {{ trans("messages.roles.managers") }}
                                @elseif($thread->chat_type == 'staffs')
                                    {{ trans("messages.roles.staffs") }}
                                @endif
                            @endif
                        </p>

                        <div class="text-xs">
                            {{ $thread->last_message ? $thread->lastMessage->created_at->diffForHumans() : $thread->updated_at->diffForHumans() }}
                        </div>

                        <div class="flex text-xs leading-4 line-clamp-2">
                            {{ $thread->lastMessage ? $thread->lastMessage->body : trans('messages.sidebar.no_message') }}
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>
    </div>
</div>
