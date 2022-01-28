<div
    x-data
    x-on:show-participants-modal.window="@this.call('showParticipantsModal')"
>
    <x-jet-modal max-width="md" wire:model.defer="modalVisible">
        <x-slot name="title">
            {{ trans('messages.participants.modal_title') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    @foreach($participants as $participant)
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

                                <p class="text-xs font-medium text-gray-500 group-hover:text-gray-700">
                                    {{ $participant->last_seen_at ? $participant->last_seen_at->format(config('setting.format.datetime')) : trans('messages.participants.never_seen') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div
                    x-data="{
                        isCustomParticipantType: @json($this->canShowUpdateForm),
                    }"
                    x-show="isCustomParticipantType" style="display: none;"
                    class="w-full"
                >
                    <label for="userIds" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('messages.create.update_participants') }}
                    </label>

                    @livewire('components.search-multi-select', [
                        'targetModel' => 'user',
                        'selected' => $participantIds,
                        'displayKey' => 'full_name',
                        'orderBy' => 'given_names',
                        'enableSearch' => true,
                        'wireKey' => 'update-participants',
                        'emitUpWhenUpdated' => [
                            'participantIds' => 'selected',
                        ],
                        'listenToEmit' => [
                            'messages.thread.selected-participants.updated',
                            'messages.thread.extra-limitor.updated',
                        ],
                        'extraLimitor' => [
                            'role' => ['User', 'Principal'],
                            'organization_id' => auth()->user()->organization_id,
                        ],
                    ])
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <div class="flex justify-end space-x-4">
                <button
                    x-data="{
                        isCustomParticipantType: @json($this->canShowUpdateForm),
                    }"
                    x-show="isCustomParticipantType" style="display: none;"
                    type="button"
                    wire:click.prevent="updateParticipants"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    {{ trans('messages.create.update_participants') }}
                </button>

                <button
                    type="button"
                    x-on:click.prevent="show = false"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    {{ trans('messages.create.close_button') }}
                </button>
            </div>
        </x-slot>
    </x-jet-modal>
</div>
