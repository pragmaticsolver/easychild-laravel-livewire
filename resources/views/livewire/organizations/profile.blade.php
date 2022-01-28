<form class="sm:mx-auto sm:w-full sm:max-w-md" wire:submit.prevent="update">
    <div class="mt-6">
        <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
            {{ trans('users.avatar_title') }}
        </label>

        <x-avatar-upload img-key="org-avatar" :default="$newAvatar ?: $avatar" wire:model.defer="newAvatar"></x-avatar-upload>
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
        <label for="leadTime" class="block mb-1 text-sm font-medium text-gray-700 leading-5">
            {{ trans('openingtimes.lead_label') }}
        </label>

        <div class="rounded-md shadow-sm">
            <input wire:model.defer="leadTime" id="leadTime" name="leadTime" type="text" required autocomplete="off" class="text-field @error('leadTime') error @enderror" />
        </div>

        @error('leadTime')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="selectionTime" class="block mb-1 text-sm font-medium text-gray-700 leading-5">
            {{ trans('openingtimes.selection_label') }}
        </label>

        <div class="rounded-md shadow-sm">
            <input wire:model.defer="selectionTime" id="selectionTime" name="selectionTime" type="text" required autocomplete="off" class="text-field @error('selectionTime') error @enderror" />
        </div>

        @error('selectionTime')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-6">
        <label for="food_locking_time" class="mb-1 block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.food_message_time_label') }}
        </label>

        <x-time-picker
            label="XX:XX"
            wire:model.defer="foodLockTime"
            class="form-select select-field block"
        ></x-time-picker>
    </div>

    <div class="mt-6">
        <label for="schedule_locking_time" class="mb-1 block text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.schedule_message_time_label') }}
        </label>

        <x-time-picker
            label="XX:XX"
            wire:model.defer="scheduleLockTime"
            class="form-select select-field block"
        ></x-time-picker>
    </div>

    <div class="mt-6">
        <label for="auto_approve_schedule" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('schedules.can_auto_approve') }}
        </label>

        <x-switch wire:model.defer="autoUpdate"></x-switch>
    </div>

    <div class="mt-6">
        <label for="auto_approve_schedule" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.profile_care_option') }}
        </label>

        <x-switch wire:model.defer="careOption"></x-switch>
    </div>

    <div class="mt-6">
        <label for="auto_approve_schedule" class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('organizations.collector_popup') }}
        </label>

        <x-switch wire:model.defer="collectorPopup"></x-switch>
    </div>

    <div class="mt-6">
        <button type="submit" class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
            {{ trans('users.update') }}
        </button>
    </div>
</form>
