<div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
    <div class="px-4 py-8 bg-white shadow sm:rounded-lg sm:px-10">
        <form wire:submit.prevent="authenticate">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('auth.email_address') }} / {{ trans('users.username') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="email" id="email" name="email" type="text" autocorrect="off" autocapitalize="none" required autofocus class="text-field @error('email') error @enderror" />
                </div>

                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label for="password" class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('auth.password') }}
                </label>

                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="password" id="password" type="password" required class="text-field @error('password') error @enderror" />
                </div>

                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between mt-6">
                <div class="flex items-center">
                    <input wire:model.defer="remember" id="remember" type="checkbox" class="form-checkbox w-4 h-4 text-indigo-600 transition duration-150 ease-in-out" />
                    <label for="remember" class="block ml-2 text-sm text-gray-900 leading-5">
                        {{ trans('auth.remember') }}
                    </label>
                </div>

                @if (Route::has('password.request'))
                    <div>
                        <a class="text-sm text-indigo-600" href="{{ route('password.request') }}">{{ trans('auth.password_forgot') }}</a>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <button
                    type="submit"
                    class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none transition duration-150 ease-in-out"
                    wire:loading.attr="disabled"
                >
                    {{ trans('auth.sign_in') }}<span wire:loading>...</span>
                </button>
            </div>
        </form>
    </div>
</div>
