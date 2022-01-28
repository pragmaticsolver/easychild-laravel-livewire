@php
    $width = 'lg:w-1/3';

    if (auth()->user()->isPrincipal()) {
        $width = 'xl:w-1/3';
    }
@endphp

@if (auth()->user()->hasAccessToService('messages'))
    <div class="mb-8 px-2 w-full md:w-1/2 {{ $width }}">
        <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
            {{ trans('dashboard.messages_title') }}
        </h2>

        <div class="bg-white overflow-hidden shadow-md border-t border-gray-200 rounded-lg">
            <ul class="px-4 py-7 space-y-4" style="min-height: 212px;">
                @forelse($messages as $message)
                    <li class="flex items-start justify-start">
                        @php
                            $avatar = $message->lastMessage ? $message->lastMessage->sender->avatar_url : null;
                        @endphp

                        @if ($avatar)
                            <img class="w-10 h-10 mr-3 rounded-full" width="48" src="{{ $avatar }}" alt="">
                        @else
                            <span class="w-10 h-10 rounded-full bg-blue-400 mr-3 overflow-hidden">
                                <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </span>
                        @endif

                        <div class="flex-grow overflow-hidden">
                            <strong class="text-sm block font-bold">{!! $message->conversationTitle() !!}</strong>
                            @if($message->unread_messages_count && $message->lastMessage)
                                <p class="text-sm truncate">{{ $message->lastMessage->body }}</p>
                            @else
                                <p class="text-sm truncate">{{ trans('dashboard.no_unread_text') }}</p>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="text-base">
                        {{ trans('dashboard.no_unread_text') }}
                    </li>
                @endforelse
            </ul>

            <div class="bg-gray-200 px-4 py-4 sm:px-6">
                <div class="text-sm leading-5">
                    <a href="{{ route('messages.index') }}"
                        class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                        {{ trans('dashboard.view_messages_link') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
