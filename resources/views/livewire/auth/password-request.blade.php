<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="px-4 py-8 bg-white shadow sm:rounded-lg sm:px-10">
        <form wire:submit.prevent="resetPassword">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('auth.email_address') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="email" id="email" name="email" type="email" required autofocus class="text-field @error('email') error @enderror" />
                </div>

                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 text-right">
                <a href="{{ route('login') }}" class="text-indigo-600">{{ trans('nav.sign_in') }}</a>
            </div>

            <div class="mt-6">
                <button
                    type="submit"
                    class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none transition duration-150 ease-in-out"
                    wire:loading.attr="disabled"
                >
                    {{ trans('auth.password_reset_button') }}<span wire:loading>...</span>
                </button>
            </div>
        </form>
    </div>
</div>
