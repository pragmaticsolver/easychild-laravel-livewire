<div
    class="flex w-full {{ $this->isSender ? ' justify-end' : '' }}"
    x-data
    x-on:user-deleted-message-{{ $message['uuid'] }}.window="@this.call('deleteMessage')"
>
    <div class="max-w-md inline-flex flex-col my-3">
        <div class="flex items-end mb-0.5 {{ $this->isSender ? ' flex-row-reverse justify-start' : '' }}">
            <div class="relative bg-blue-400 h-8 rounded-full text-white flex-shrink-0 w-8 {{ $this->isSender ? ' ml-3' : ' mr-3' }}">
                @if ($this->isSender && !$message['deleted_at'])
                    <button
                        class="absolute flex items-center justify-center left-full bottom-full w-5 h-5 bg-red-600 text-white rounded-full -ml-3 -mb-2 hover:outline-none outline-none hover:bg-red-500"
                        x-on:click.prevent="$dispatch('conversation-message-delete-confirm-modal-open', {
                            'title': '{{ trans("messages.thread.delete_confirm_title") }}',
                            'description': '{{ trans("messages.thread.delete_confirm_description") }}',
                            'event': 'user-deleted-message-{{ $message['uuid'] }}',
                        })"
                    >
                        <x-heroicon-o-trash class="w-3 h-3" />
                    </button>
                @endif

                @if ($message['sender_avatar'])
                    <img src="{{ asset('avatars/' . $message['sender_avatar']) }}" alt="{{ $message['sender_name'] }}" class="block h-8 w-8 rounded-full">
                @else
                    <img src="{{ 'https://ui-avatars.com/api/?name=' . urlencode($message['sender_full_name']) . '&color=000000&background=E5E7EB' }}" alt="{{ $message['sender_name'] }}" class="block h-8 w-8 rounded-full">
                    {{-- <span class="block overflow-hidden rounded-full">
                        <svg class="block h-8 w-8 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </span> --}}
                @endif
            </div>

            <div class="rounded-md p-2 text-sm border text-white {{ $message['deleted_at'] ? ' border-red-400 bg-red-400' : ' border-indigo-400 bg-indigo-400' }}">
                @if ($message['deleted_at'])
                    {{ trans('messages.thread.message_deleted') }}
                @else
                    @if($message['type'] == 'text')
                        {!! nl2br($message['body']) !!}
                    @elseif($message['type'] == 'image')
                        <img src="{{url('attachment/' . $message['uuid'])}}" alt="img" wire:click="showImage" style="cursor: zoom-in" />
                        {!! nl2br($message['body']) !!}
                    @elseif($message['type'] == 'pdf')
                        <span class="flex items-center">
                            {!! nl2br($message['body']) !!}
                            <a
                                title="{{ trans('informations.view') }}" href="#"
                                class="ml-5 p-1 cursor-pointer"
                                wire:click.prevent="showPDFAttachment"
                            >
                                <x-heroicon-o-eye class="w-5 h-5"></x-heroicon-o-eye>
                            </a>
                        </span>
                    @else
                        <span class="flex items-center">
                            {!! nl2br($message['body']) !!}  <span wire:click="download" class="ml-5 p-1 cursor-pointer rounded-full border border-white" ><x-heroicon-o-download class="w-5 h-5" /></span>
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <div class="pt-1 text-right text-xs px-2">
            @if ($this->isSender)
                {{ trans('messages.thread.you_title') }} &bull;
            @else
                @php
                    $messageRole = (string) Str::of($message['role'])->lower();
                @endphp
                {{ $message['sender_name'] }} &bull;

                @if($messageRole == 'user')
                    {{ trans("messages.thread.user_role") }}
                @elseif($messageRole == 'parent')
                    {{ trans("messages.thread.parent_role") }}
                @elseif($messageRole == 'manager')
                    {{ trans("messages.thread.manager_role") }}
                @elseif($messageRole == 'principal')
                    {{ trans("messages.thread.principal_role") }}
                @elseif($messageRole == 'admin')
                    {{ trans("messages.thread.admin_role") }}
                @endif

                &bull;
            @endif

            <span>
                @if($message['deleted_at'])
                    {{ $message['deleted_at_formatted'] }}
                @else
                    {{ $message['created_at_formatted'] }}
                @endif
            </span>
        </div>

        @if(auth()->user()->id == $message['sender_id'])
            <div class="pt-1 text-right text-xs px-2 flex justify-end cursor-pointer" wire:click="showStatus">
{{--                {{ trans('messages.thread.received_by') }}: {{ $message['received_by'] && $message['received_by'] > 0 ? ($message['received_by'] - 1) : $message['received_by'] }} <br>--}}
{{--                {{ trans('messages.thread.read_by') }}: {{ $message['read_by'] && $message['read_by'] > 0 ? ($message['read_by'] - 1) : $message['read_by'] }}--}}
                @if($message['read_by'] == count($participants))
                    <img src="{{asset('img/icons/read-tick.svg')}}" alt="read" />
                @else
                    <img src="{{asset('img/icons/tick.svg')}}" alt="unread"  />
                @endif
            </div>
        @endif
    </div>
</div>
