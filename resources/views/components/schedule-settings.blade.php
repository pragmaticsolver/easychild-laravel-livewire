@if($eatsOnsiteType === 'user')
    <div class="mt-6">
        <label class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('schedules.settings.user-eats-onsite-title') }}
        </label>

        <table>
            <thead>
                <tr>
                    <td class="text-xs px-2 py-1 text-black">{{ trans('schedules.breakfast') }}</td>
                    <td class="text-xs px-2 py-1 text-black">{{ trans('schedules.lunch') }}</td>
                    <td class="text-xs px-2 py-1 text-black">{{ trans('schedules.dinner') }}</td>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="px-2 py-1 text-center">
                        <x-toggle-options
                            :dim="! $eatsOnsiteOrgDefaults['breakfast']"
                            :default-value="$eatsOnsite['breakfast']"
                            wire:model.defer="eatsOnsite.breakfast"
                            :options="[null, false]"
                            :backgrounds="['bg-green-600', 'bg-red-500']"
                        >
                            <x-zondicon-location-food class="w-4 h-4" />
                        </x-toggle-options>
                    </td>

                    <td class="px-2 py-1 text-center">
                        <x-toggle-options
                            :dim="! $eatsOnsiteOrgDefaults['lunch']"
                            :default-value="$eatsOnsite['lunch']"
                            wire:model.defer="eatsOnsite.lunch"
                            :options="[null, false]"
                            :backgrounds="['bg-green-600', 'bg-red-500']"
                        >
                            <x-zondicon-location-food class="w-4 h-4" />
                        </x-toggle-options>
                    </td>

                    <td class="px-2 py-1 text-center">
                        <x-toggle-options
                            :dim="! $eatsOnsiteOrgDefaults['dinner']"
                            :default-value="$eatsOnsite['dinner']"
                            wire:model.defer="eatsOnsite.dinner"
                            :options="[null, false]"
                            :backgrounds="['bg-green-600', 'bg-red-500']"
                        >
                            <x-zondicon-location-food class="w-4 h-4" />
                        </x-toggle-options>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@else
    <div class="mt-6">
        <label class="block mb-2 text-sm font-medium text-gray-700 leading-5">
            {{ trans('schedules.settings.user-eats-onsite-title') }}
        </label>

        <table>
            <thead>
                <tr>
                    <td class="text-xs px-2 py-1 text-black">{{ trans('schedules.breakfast') }}</td>
                    <td class="text-xs px-2 py-1 text-black">{{ trans('schedules.lunch') }}</td>
                    <td class="text-xs px-2 py-1 text-black">{{ trans('schedules.dinner') }}</td>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td class="px-2 py-1 text-center">
                        <x-toggle-options
                            :default-value="$eatsOnsite['breakfast']"
                            wire:model.defer="eatsOnsite.breakfast"
                        >
                            <x-zondicon-location-food class="w-4 h-4" />
                        </x-toggle-options>
                    </td>

                    <td class="px-2 py-1 text-center">
                        <x-toggle-options
                            :default-value="$eatsOnsite['lunch']"
                            wire:model.defer="eatsOnsite.lunch"
                        >
                            <x-zondicon-location-food class="w-4 h-4" />
                        </x-toggle-options>
                    </td>

                    <td class="px-2 py-1 text-center">
                        <x-toggle-options
                            :default-value="$eatsOnsite['dinner']"
                            wire:model.defer="eatsOnsite.dinner"
                        >
                            <x-zondicon-location-food class="w-4 h-4" />
                        </x-toggle-options>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif

<div class="mt-6">
    <label for="user_availability" class="block text-sm font-medium text-gray-700 leading-5">
        {{ trans('schedules.settings.availability-setting') }}
    </label>

    <div class="mt-1 rounded-md shadow-sm">
        <select wire:model.defer="availability" id="user_availability" name="availability" class="form-select select-field @error('availability') error @enderror">
            @if($eatsOnsiteType === 'user')
                <option value="">{{ trans('schedules.settings.availability-org-defaults') }}</option>
            @endif
            @foreach($availabilityOptions as $option)
                <option value="{{ $option['value'] }}">
                    @if($option['translation'] == 'extras.user-availability.available')
                        {{ trans('extras.user-availability.available') }}
                    @elseif($option['translation'] == 'extras.user-availability.not-available')
                        {{ trans('extras.user-availability.not-available') }}
                    @elseif($option['translation'] == 'extras.user-availability.not-available-with-time')
                        {{ trans('extras.user-availability.not-available-with-time') }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    @error('availability')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
