<form wire:submit.prevent="logoutOtherBrowserSessions">
    <x-jet-modal wire:model.defer="logoutOtherDeviceModalActive">
        <x-slot name="title">
            {{ trans('extras.logout-other.modal.title') }}
        </x-slot>

        <x-slot name="content">
            {{ trans('extras.logout-other.modal.content') }}

            <div class="mt-4" x-data="{}" x-on:confirming-logout-other-browser-sessions.window="setTimeout(() => $refs.password.focus(), 250)">
                <label for="userPassword" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('users.password') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="userPassword" x-ref="password" id="userPassword" name="userPassword" type="password" required autocomplete="new-password" class="text-field @error('userPassword') error @enderror" />
                </div>

                @error('userPassword')
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
                    {{ trans('extras.logout-other.modal.approve') }}
                </button>

                <button
                    type="button"
                    wire:click.prevent="$toggle('logoutOtherDeviceModalActive', false)"
                    class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    {{ trans('extras.logout-other.modal.cancel') }}
                </button>
            </div>
        </x-slot>
    </x-jet-dialog-modal>
</form>
