@props([
    'width' => 200,
    'height' => 200,
    'default' => null,
    'imgKey' => null,
])

<div
    class="mt-1"
    {{ $attributes }}
>
    <div
        x-data="imageUploader('{{ $default }}', {{ $width }}, {{ $height }}, '{{ $imgKey }}')"
        x-init="mounted()"
        class="mx-auto relative"
        :class="{'group': !croppie}"
        x-on:croppie-reset-event-fired.window="resetCropper()"
        wire:ignore
    >
        <div x-show="(defaultImage || image) && !croppie">
            <img style="{{ $width ? ('width:'. $width .'px;') : '' }} {{ $height ? ('height:'. $height .'px;') : '' }}" class="block mx-auto" :src="image ? image : defaultImage" alt="">
        </div>

        <div wire:ignore>
            <div x-ref="image" class="w-full"></div>

            <template x-if="croppie">
                <div class="text-center space-y-2 space-x-2">
                    <button type="button" x-show="croppie" x-on:click.prevent="rotateImage">
                        <x-heroicon-o-refresh class="w-5 h-5"></x-heroicon-o-refresh>
                    </button>

                    <button type="button" x-show="croppie" x-on:click.prevent="cropImage($dispatch)">
                        <x-heroicon-o-check class="w-5 h-5"></x-heroicon-o-check>
                    </button>
                </div>
            </template>
        </div>

        <svg x-show="!image && !defaultImage" style="{{ $width ? ('width:'. $width .'px;') : '' }} {{ $height ? ('height:'. $height .'px;') : '' }}" class="text-gray-300 block mx-auto" fill="currentColor" viewBox="0 0 24 24">
            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>

        <input x-on:change="onChanged($event.target.files)" type="file" :id="modelId" class="opacity-0 absolute invisible">

        <div class="absolute inset-0 flex bg-black bg-opacity-25 items-center justify-center group-hover:visible invisible">
            <label :for="modelId" class="cursor-pointer py-2 px-3 border border-gray-300 rounded-md text-sm leading-4 font-medium text-white focus:outline-none transition duration-150 ease-in-out">{{ trans('users.profile_avatar_change') }}</label>
        </div>
    </div>
</div>
