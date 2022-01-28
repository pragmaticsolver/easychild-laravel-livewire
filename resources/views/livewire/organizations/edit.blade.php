<form class="sm:mx-auto sm:w-full sm:max-w-md" wire:submit.prevent="updateOrg">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.name') }}
        </label>

        <div class="mt-1 rounded-md shadow-sm">
            <input wire:model.defer="name" id="name" name="name" type="text" required class="text-field @error('name') error @enderror" />
        </div>

        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="house_no" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.house_no') }}
        </label>

        <div class="mt-1 rounded-md shadow-sm">
            <input wire:model.defer="house_no" id="house_no" name="house_no" type="text" required autocomplete="off" class="text-field @error('house_no') error @enderror" />
        </div>

        @error('house_no')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="street" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.street') }}
        </label>

        <div class="mt-1 rounded-md shadow-sm">
            <input wire:model.defer="street" id="street" name="street" type="text" required autocomplete="off" class="text-field @error('street') error @enderror" />
        </div>

        @error('street')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="zip_code" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.zip_code') }}
        </label>

        <div class="mt-1 rounded-md shadow-sm">
            <input wire:model.defer="zip_code" id="zip_code" name="zip_code" type="text" required autocomplete="off" class="text-field @error('zip_code') error @enderror" />
        </div>

        @error('zip_code')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="city" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.city') }}
        </label>

        <div class="mt-1 rounded-md shadow-sm">
            <input wire:model.defer="city" id="city" name="city" type="text" required autocomplete="off" class="text-field @error('city') error @enderror" />
        </div>

        @error('city')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="phone" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.phone') }}
        </label>

        <div class="mt-1 rounded-md shadow-sm">
            <input wire:model.defer="phone" id="phone" name="phone" type="text" required autocomplete="off" class="text-field @error('phone') error @enderror" />
        </div>

        @error('phone')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @include('components.schedule-settings', [
        'eatsOnsiteType' => 'org'
    ])

    <div class="mt-6">
        <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('users.avatar_title') }}
        </label>

        <x-avatar-upload :default="$newAvatar ?: $avatar" wire:model.defer="newAvatar"></x-avatar-upload>
    </div>

    <div class="mt-6">
        <label for="messages_service" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.messages_service') }}
        </label>

        <x-switch wire:model.defer="serviceMessages"></x-switch>
    </div>

    <div class="mt-6">
        <label for="informations_service" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.informations_service') }}
        </label>

        <x-switch wire:model.defer="serviceInformations"></x-switch>
    </div>

    <div class="mt-6">
        <button type="submit" class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
            {{ trans('organizations.update') }}
        </button>
    </div>
</form>
