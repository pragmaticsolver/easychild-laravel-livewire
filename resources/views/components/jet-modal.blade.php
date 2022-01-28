@props([
    'id',
    'maxWidth',
    'cancelEvent' => null,
    'disableTitle' => false,
    'mainClass' => 'fixed top-0 inset-x-0 sm:p-2 md:p-4 z-50 sm:px-0 sm:flex sm:items-top sm:justify-center max-h-vhscreen custom-scrollbar overflow-x-hidden overflow-y-auto',
])

@php
$id = $id ?? md5($attributes->wire('model'));

switch ($maxWidth ?? '3xl') {
    case 'sm':
        $maxWidth = 'sm:max-w-sm';
        break;
    case 'md':
        $maxWidth = 'sm:max-w-md';
        break;
    case 'lg':
        $maxWidth = 'sm:max-w-lg';
        break;
    case 'xl':
        $maxWidth = 'sm:max-w-xl';
        break;
    case '2xl':
        $maxWidth = 'sm:max-w-2xl';
        break;
    case '4xl':
        $maxWidth = 'sm:max-w-4xl';
        break;
    case '5xl':
        $maxWidth = 'sm:max-w-4xl lg:max-w-5xl';
        break;
    case '3xl':
    default:
        $maxWidth = 'sm:max-w-3xl';
        break;
}
@endphp

<div
    x-data="{
        show: @entangle($attributes->wire('model')),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'

            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    id="{{ $id }}"
    class="{{ $mainClass }}"
    style="display: none;"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        @if ($cancelEvent)
            wire:click.prevent="{{ $cancelEvent }}"
        @else
            x-on:click="show = false"
        @endif
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="relative bg-white rounded-lg shadow-xl sm:w-full h-full {{ $maxWidth }}"
        x-transition:enter="transform transition-all ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transform transition-all ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        style="display: none;"
    >
        <div class="{{ $disableTitle ? '' : 'p-3 md:px-6 md:py-4' }}">
            @if (! $disableTitle)
                <div class="text-sm md:text-lg flex justify-between items-center">
                    <div class="truncate">
                        @if(isset($title))
                            {{ $title }}
                        @endif
                    </div>

                    <button
                        @if ($cancelEvent)
                            wire:click.prevent="{{ $cancelEvent }}"
                        @else
                            x-on:click="show = false"
                        @endif
                        type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150 ml-4" aria-label="Close"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="{{ $disableTitle ? '' : 'mt-4' }}">
                {{ $content }}
            </div>
        </div>

        @if (isset($footer))
            <div class="px-6 py-4 bg-gray-100 text-right rounded-b-lg">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
