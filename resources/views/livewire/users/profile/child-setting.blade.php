<div class=""
    x-data="{
        active: false,
    }"
>
    <div class="flex items-center bg-gray-500 p-px rounded-l-full">
        <div class="w-9 rounded-full overflow-hidden flex-shrink-0 mr-3">
            <img src="{{ $child->avatar_url }}" class="rounded-full align-top w-9 h-9" alt="{{ $child->full_name }}">
        </div>

        <div class="flex-1 text-white font-medium">{{ $child->full_name }}</div>

        <div class="flex-shrink-0 w-10">
            <button
                type="button"
                class="focus:outline-none block"
                x-on:click.prevent="active = !active"
            >
                <x-heroicon-o-chevron-down class="w-5 h-5" />
            </button>
        </div>
    </div>

    <div class="py-4" x-show="active">
        <div
            x-on:avatar-input-changed="@this.set('newAvatar', $event.detail)"
        >
            <label class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.avatar_title') }}
            </label>

            <x-avatar-upload
                :default="$newAvatar ?: $avatar"
                img-key="user-avatar"
            ></x-avatar-upload>
        </div>

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
                                :dim="! $this->eatsOnsiteOrgDefaults['breakfast']"
                                :default-value="$eatsOnsite['breakfast']"
                                wire:model="eatsOnsite.breakfast"
                                :options="[null, false]"
                                :backgrounds="['bg-green-600', 'bg-red-500']"
                            >
                                <x-zondicon-location-food class="w-4 h-4" />
                            </x-toggle-options>
                        </td>

                        <td class="px-2 py-1 text-center">
                            <x-toggle-options
                                :dim="! $this->eatsOnsiteOrgDefaults['lunch']"
                                :default-value="$eatsOnsite['lunch']"
                                wire:model="eatsOnsite.lunch"
                                :options="[null, false]"
                                :backgrounds="['bg-green-600', 'bg-red-500']"
                            >
                                <x-zondicon-location-food class="w-4 h-4" />
                            </x-toggle-options>
                        </td>

                        <td class="px-2 py-1 text-center">
                            <x-toggle-options
                                :dim="! $this->eatsOnsiteOrgDefaults['dinner']"
                                :default-value="$eatsOnsite['dinner']"
                                wire:model="eatsOnsite.dinner"
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
    </div>
</div>
