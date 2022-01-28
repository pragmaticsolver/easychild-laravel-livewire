<div class="w-full relative sm:w-auto flex-1 sm:border-l border-gray-200 h-full flex flex-col">
    <div
        class="message-area overflow-x-hidden flex flex-col-reverse flex-1 custom-scrollbar overflow-y-auto border-t border-gray-200 px-3"
        :class="{'custom-scrollbar': isWinOS}"
        x-data="{isWinOS: true}"
        x-cloak
        x-init="isWinOS = navigator.platform.indexOf('Win') > -1"
        x-on:message-scroll-to-latest.window="$el.scrollTop = $el.scrollHeight"
    >
        <div>
            @if (auth()->user()->isManager())
                <a href="#" x-on:click.prevent="$dispatch('show-participants-modal')" class="absolute flex items-center right-0 top-0 rounded-bl-md rounded-tr-md p-2 bg-blue-700 text-white outline-none focus:outline-none">
                    {{ trans('messages.participants.view_btn') }}

                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </a>
            @endif
        </div>

        @unless($messages)
            <div class="flex w-full">
                <div class="max-w-md inline-flex flex-col my-3">
                    <div class="rounded-md p-2 text-sm border border-indigo-400 bg-indigo-400 text-white">
                        {{ trans('messages.thread.no_messages') }}
                    </div>
                </div>
            </div>
        @else
            <div>
                @foreach($messages as $message)
                    @if($loop->first)
                        <div class="pt-10"></div>
                    @endif
                    @livewire('messages.thread-item', compact('message'), key($message['uuid']))
                @endforeach
            </div>
        @endif

        @if ($hasNext)
            <div class="w-full py-3 text-center">
                <button class="text-sm font-medium text-gray-500 outline-none focus:outline-none hover:text-gray-700" wire:click="loadMoreMessages">{{ trans('messages.thread.load_more') }}</button>
            </div>
        @endif
    </div>

    @livewire('components.file-modal')

    @livewire('messages.participant-modal')

    @livewire('messages.send')
</div>
