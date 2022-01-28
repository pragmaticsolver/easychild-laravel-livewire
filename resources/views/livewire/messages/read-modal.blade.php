<x-jet-modal wire:model.defer="showModal" max-width="2xl" mainClass="fixed top-40 lg:top-72 inset-x-0 sm:p-2 md:p-4 z-50 sm:px-0 sm:flex sm:items-top sm:justify-center max-h-vhscreen custom-scrollbar overflow-x-hidden overflow-y-auto">
    <x-slot name="title">
        <div class="flex items-center justify-between">
            <b>{{trans('messages.message_from', ['name' => auth()->user()->fullname])}} </b>
        </div>
    </x-slot>

    <x-slot name="content">
        <div class="space-y-4">
            <div>
                @foreach($participants as $participant)
                    @if($participant->id != auth()->user()->id)
                        <div wire:key="user-participants-{{ $participant->uuid }}" class="flex items-center">
                            <div>
                                @if($participant->avatar_url)
                                    <img class="inline-block h-9 w-9 rounded-full" src="{{ $participant->avatar_url }}" alt="">
                                @else
                                    <svg class="inline-black h-9 w-9 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                @endif
                            </div>

                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-700 group-hover:text-gray-900">
                                    {{ $participant->full_name }} ({{ trans('extras.role_' . Str::of($participant->role)->lower()) }})
                                </p>
                                <div class="flex items-center justify-between w-32">
                                    <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                        {{ $participant->last_seen_at ? $participant->last_seen_at->format(config('setting.format.datetime')) : trans('messages.participants.never_seen') }}
                                    </p>
                                    @if($participant->read)
                                        <img src="{{asset('img/icons/read-tick.svg')}}" alt="read" />
                                    @else
                                        <img src="{{asset('img/icons/tick.svg')}}" alt="unread" />
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </x-slot>
{{--    <x-slot name="footer">--}}
{{--        <div class="text-right">--}}
{{--            <button--}}
{{--                type="button"--}}
{{--                wire:click.prevent="close"--}}
{{--                class="inline-flex justify-center mr-1 px-7 py-1 text-sm font-medium text-white bg-indigo-400 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-600 focus:shadow-outline-blue active:bg-indigo-600 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"--}}
{{--            >Close</button>--}}
{{--        </div>--}}
{{--    </x-slot>--}}
</x-jet-modal>
