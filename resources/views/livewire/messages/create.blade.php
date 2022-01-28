<div
    class="fixed z-20 inset-0 px-4 pb-6 sm:p-0 flex items-center justify-center"
    x-show="enabled"
    :class="{'invisible': ! enabled}"
    x-data="{
        enabled: @entangle('enabled'),
        creatingMultiple: @entangle('creatingMultiple'),
        isWinOs: true,
    }"
    x-cloak
    x-init="
        isWinOS = navigator.platform.indexOf('Win') > -1;
    "
    x-on:show-create-new-room-modal.window="@this.call('showCreateNewRoomModal')"
>
    <div
        x-show="enabled"
        class="fixed inset-0 transition-opacity"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.prevent="enabled = false"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="enabled"
        class="max-h-mscreen relative overflow-y-auto bg-white rounded-lg px-4 pt-5 pb-4 m-auto my-4 shadow-xl transition-all max-w-md w-full sm:p-6"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        :class="{'custom-scrollbar': isWinOS}"
    >
        <div>
            <h3 class="text-sm leading-5 font-medium text-gray-700 mb-3 flex items-center justify-between" id="modal-headline">
                <span class="pr-3">{{ trans('messages.create.title') }}</span>

                <button x-on:click.prevent="enabled = false" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150" aria-label="Close">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </h3>

            <div>
                @if ($needsUserSelector)
                    <div>
                        @if (auth()->user()->isManager())
                            <div class="mt-3">
                                <label for="chat_type_multiple" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
                                    {{ trans('messages.create.chat_type_multiple') }}
                                </label>

                                <x-switch wire:model="creatingMultiple"></x-switch>
                            </div>
                        @endif
                    </div>

                    <div style="display: none;" x-show="creatingMultiple">
                        <div class="mt-3">
                            <label for="chatTitle" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('messages.create.title_label') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <input wire:model.defer="chatTitle" id="chatTitle" name="chatTitle" type="text" autocomplete="off" class="text-field @error('chatTitle') error @enderror" />
                            </div>

                            @error('chatTitle')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <label for="userIds" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('messages.create.participants') }}
                            </label>

                            @livewire('components.search-multi-select', [
                                'targetModel' => 'user',
                                'selected' => $userIds,
                                'displayKey' => 'full_name',
                                'orderBy' => 'given_names',
                                'enableSearch' => true,
                                'emitUpWhenUpdated' => [
                                    'userIds' => 'selected',
                                ],
                                'wireKey' => 'create-participants',
                                'listenToEmit' => [
                                    'messages.create.selected-participants.updated',
                                ],
                                'extraLimitor' => [
                                    'role' => ['User', 'Principal'],
                                    'organization_id' => auth()->user()->organization_id,
                                ],
                            ])

                            @error('userIds')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div style="display: none;" x-show="! creatingMultiple">
                        <div class="mt-3">
                            <label for="userId" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('messages.create.user_label') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                @livewire('components.search-select', [
                                    'provider' => $this->usersListProvider,
                                    'selected' => $userId,
                                    'emitUpWhenUpdated' => 'userId',
                                    'listenToEmit' => 'messages.create.userId.updated',
                                    'enableAnimation' => false
                                ])
                            </div>

                            @error('userId')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @else
                    <div class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('messages.room_type.your_principal') }}
                        </label>
                    </div>
                @endif
            </div>
        </div>
        <div class="mt-5 sm:mt-6">
            <div class="flex justify-end -mx-2">
                <div class="px-2">
                    <button type="button"
                        x-on:click.prevent="enabled = false"
                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-red-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-indigo transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                    >
                        {{ trans('messages.create.cancel_button') }}
                    </button>
                </div>

                <div class="px-2">
                    <button type="button"
                        wire:click="createNewRoom"
                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-indigo-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                    >
                        {{ trans('messages.create.create_button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
