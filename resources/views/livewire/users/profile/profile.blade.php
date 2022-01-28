<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <form wire:submit.prevent="update" autocomplete="off">
        <div>
            <label for="given_names" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.given_name') }}
            </label>

            <div class="mt-1 rounded-md shadow-sm">
                <input wire:model.defer="given_names" id="given_names" type="text" required autocomplete="off" class="text-field @error('given_names') error @enderror" />
            </div>

            @error('given_names')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label for="last_name" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.last_name') }}
            </label>

            <div class="mt-1 rounded-md shadow-sm">
                <input wire:model.defer="last_name" id="last_name" type="text" required autocomplete="off" class="text-field @error('last_name') error @enderror" />
            </div>

            @error('last_name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            @if (! auth()->user()->isPrincipal())
                <div class="mt-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.email') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.defer="email" id="email" type="email" required autocomplete="off" class="text-field @error('email') error @enderror" />
                    </div>

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>

        <div>
            @if (auth()->user()->isParent())
                <div class="mt-6">
                    <label for="phone" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.phone') }}
                    </label>

                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500 sm:text-sm">
                            +49 (0)
                        </span>
                        <input wire:model.defer="phone" id="phone" type="text" autocomplete="off" class="text-field rounded-l-none flex-1 @error('phone') error @enderror" />
                    </div>

                    @error('phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </div>

        <div class="mt-6">
            <label for="lang" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.lang') }}
            </label>

            <div class="mt-1">
                <div class="relative z-0 inline-flex shadow-sm rounded-md">
                    @foreach($this->allAvailableLanguages as $langItem)
                        <button
                            type="button"
                            class="
                                relative inline-flex items-center px-4 py-2 border
                                border-gray-300 text-sm font-medium uppercase
                                {{ $langItem === $lang ? 'bg-indigo-500 text-white' : ' bg-white text-gray-700' }}
                                hover:bg-indigo-500 hover:text-white
                                focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500
                                {{ $loop->first ? ' rounded-l-md' : '' }}
                                {{ $loop->last ? ' rounded-r-md' : '' }}
                            "
                            title="{{ trans('extras.languages.' . $langItem) }}"
                            wire:click="switchLanguage('{{ $langItem }}')"
                        >
                            {{ $langItem }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 leading-5 mb-2">
                {{ trans('users.notification.label_title') }}
            </label>

            {{-- @livewire('components.service-worker') --}}
            @if (! auth()->user()->isPrincipal())
                <div class="flex text-sm items-center mb-4">
                    <x-switch class="mr-3" wire:model="mail"></x-switch>

                    <span>{{ trans('users.notification.send_email') }}</span>
                </div>
            @endif

            <div>
                @if (auth()->user()->isParent())
                    <div class="flex text-sm items-center mb-4">
                        <x-switch class="mr-3" wire:model="sms"></x-switch>

                        <span>{{ trans('users.notification.send_sms') }}</span>
                    </div>
                @endif
            </div>

            <div class="flex text-sm items-center">
                <x-switch class="mr-3" wire:model="pushNotification"></x-switch>

                <span>{{ trans('users.notification.push_notification') }}</span>
            </div>
        </div>

        {{-- <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 leading-5 mb-3">
                {{ trans('users.opt-email-notification') }}
            </label>

            <div class="flex items-center">
                <input wire:model.defer="mail" id="opt_mail" type="checkbox" class="form-checkbox w-4 h-4 text-indigo-600 transition duration-150 ease-in-out" />

                <label for="opt_mail" class="block ml-2 text-sm text-gray-900 leading-5">
                    {{ trans('users.opt-in-or-out') }}
                </label>
            </div>
        </div> --}}

        {{-- <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 leading-5 mb-3">
                {{ trans('users.opt-push-notification') }}
            </label>

            <div class="block">
                @livewire('components.service-worker')
            </div>
        </div> --}}

        @if (! auth()->user()->isContractor())
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 leading-5">
                    {{ trans('extras.ical-link') }}
                </label>

                <div x-data="{'iCalRoute': '{{ $iCalRoute }}'}" class="mt-3 relative">
                    <button x-on:click.prevent="selectText($refs.ical);document.execCommand('copy');" type="button" class="absolute right-2 bottom-2 flex items-center justify-center p-1.5 rounded-full text-white bg-blue-600 hover:bg-blue-700 outline-none focus:outline-none shadow-md">
                        <x-heroicon-o-clipboard-check class="h-5 w-5" />
                    </button>

                    <div x-ref="ical" class="text-gray-600 rounded-lg bg-gray-200 p-2 break-words text-xss" x-text="iCalRoute"></div>
                </div>
            </div>
        @endif

        {{-- <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('extras.logout-other.main_label') }}
            </label>

            <div class="mt-6 flex justify-end">
                <button type="button" wire:click.prevent="showLogoutOtherDeviceModal" class="flex mb-2 justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                    {{ trans('extras.logout-other.main_btn') }}
                </button>
            </div>
        </div> --}}

        <div class="mt-6">
            <label for="password" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.password') }} <small>({{ trans('users.password_change_info') }})</small>
            </label>

            <div class="mt-1 rounded-md shadow-sm">
                <input wire:model.defer="password" id="password" name="user_password" type="password" autocomplete="new-password" class="text-field @error('password') error @enderror" />
            </div>

            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.password_confirmation') }}
            </label>

            <div class="mt-1 rounded-md shadow-sm">
                <input wire:model.defer="password_confirmation" id="password_confirmation" type="password" autocomplete="off" class="text-field @error('password_confirmation') error @enderror" />
            </div>

            @error('password_confirmation')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.avatar_title') }}
            </label>

            <x-avatar-upload :default="$newAvatar ?: $avatar" img-key="user-avatar" wire:model.defer="newAvatar"></x-avatar-upload>
        </div>

        <div class="mt-6">
            <button type="submit" class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                {{ trans('users.update') }}
            </button>
        </div>
    </form>

    {{-- @include('partials.user-profile.logout-other-browser-form') --}}
</div>
