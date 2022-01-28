@props([
    'disabled' => false,
    'dispatchEvent' => '',
    'alpineVar' => 'state',
])

<div class="inline-flex align-top flex-shrink-0">
    <span
        @if($attributes->has('wire:model') || $attributes->has('wire:model.defer'))
            {{ $attributes->merge(['class' => 'relative inline-flex h-6 w-11 border-2 border-transparent rounded-full transition-colors ease-in-out duration-200 focus:outline-none focus:shadow-outline shadow-md ' . ($disabled ? ' cursor-default opacity-50' : ' cursor-pointer')]) }}
            x-data="{
                '{{ $alpineVar }}': @entangle($attributes->wire('model'))
            }"

            @if (! $disabled && $dispatchEvent)
                x-init="$watch('{{ $alpineVar }}', value => $dispatch('{{ $dispatchEvent }}', value))"
            @endif
            x-cloak
            @if (! $disabled)
                x-on:click.prevent="{{ $alpineVar }} = !{{ $alpineVar }}"
            @endif
            x-bind:class="{
                'bg-gray-400': !{{ $alpineVar }},
                'bg-blue-600': {{ $alpineVar }}
            }"
        @else
            {{ $attributes->merge(['class' => 'relative inline-flex h-6 w-11 border-2 border-transparent rounded-full transition-colors ease-in-out duration-200 focus:outline-none focus:shadow-outline shadow-md']) }}
        @endif
    >
        <span
            class="translate-x-0 relative inline-block h-5 w-5 rounded-full bg-white shadow transform transition ease-in-out duration-200"
            x-bind:class="{
                'translate-x-0': !{{ $alpineVar }},
                'translate-x-5': {{ $alpineVar }}
            }"
        >
            <span
                class="opacity-100 ease-in duration-200 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity"
                x-bind:class="{
                    'opacity-100 ease-in duration-200': !{{ $alpineVar }},
                    'opacity-0 ease-out duration-100': {{ $alpineVar }}
                }"
            >
                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                    <path d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>

            <span
                class="opacity-0 ease-out duration-100 absolute inset-0 h-full w-full flex items-center justify-center transition-opacity"
                x-bind:class="{
                    'opacity-0 ease-out duration-100': !{{ $alpineVar }},
                    'opacity-100 ease-in duration-200': {{ $alpineVar }}
                }"
            >
                <svg class="h-3 w-3 text-brand" fill="currentColor" viewBox="0 0 12 12">
                    <path d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z" />
                </svg>
            </span>
        </span>
    </span>
</div>
