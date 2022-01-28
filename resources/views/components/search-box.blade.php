<div {{ $attributes->merge(['class' => 'relative']) }}>
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <x-heroicon-s-search class="h-5 w-5 text-gray-400" />
    </div>

    <input wire:model.debounce.500ms="search" class="form-input block w-full pl-10 text-sm leading-6" placeholder="{{ $placeholder }}" />

    <div wire:loading.class.remove="opacity-0" class="opacity-0 absolute inset-y-0 right-0 pr-3 leading-none flex items-center pointer-events-none">
        <x-loading></x-loading>
    </div>
</div>
