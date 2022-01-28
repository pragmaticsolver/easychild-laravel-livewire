<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="px-4 py-8 bg-white shadow sm:rounded-lg sm:px-10">
        <p class="mb-4">
            {{ trans('users.parent.signup_paragraph') }}
        </p>

        <form wire:submit.prevent="submit">
            <div>
                <label for="given_names" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('users.given_name') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="user.given_names" id="given_names" name="given_names" type="text" autocomplete="given-name" required autofocus class="text-field @error('user.given_names') error @enderror" />
                </div>

                @error('user.given_names')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="last_name" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('users.last_name') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="user.last_name" id="last_name" name="last_name" type="text" autocomplete="family-name" required class="text-field @error('user.last_name') error @enderror" />
                </div>

                @error('user.last_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-sm mt-4">
                <p>{{ trans('users.password_change_info') }}</p>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('users.password') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="password" id="password" name="password" type="password" autocomplete="new-password" class="text-field @error('password') error @enderror" />
                </div>

                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('users.password_confirmation') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" class="text-field @error('password_confirmation') error @enderror" />
                </div>

                @error('password_confirmation')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <button
                    type="submit"
                    class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none transition duration-150 ease-in-out"
                    wire:loading.attr="disabled"
                >
                    {{ trans('users.update') }}<span wire:loading>...</span>
                </button>
            </div>
        </form>
    </div>
</div>
