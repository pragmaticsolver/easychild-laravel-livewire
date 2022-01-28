@props([
    'selectItems' => collect([]),
    'currentValue' => null,
    'valueKey' => 'value',
    'textKey' => 'text',
])

@php
    $tailWindData = collect([
        'selectItems' => $selectItems,
        'valueKey' => $valueKey,
        'textKey' => $textKey,
        'value' => $currentValue,
    ]);
@endphp

<div
    x-data="TailwindComponents.customSelect({{ $tailWindData }})"
    x-init="init()"
>
    <div class="mt-1 relative">
        <button type="button" x-ref="button" @keydown.arrow-up.stop.prevent="onButtonClick()"
            @keydown.arrow-down.stop.prevent="onButtonClick()" @click="onButtonClick()" aria-haspopup="listbox"
            :aria-expanded="open" aria-expanded="true" aria-labelledby="listbox-label"
            class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
            <span x-text="selectItems[selected]['text']" class="block truncate">
                Tom Cook
            </span>

            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </span>
        </button>

        <div
            x-show="open"
            @click.away="open = false"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute mt-1 w-full rounded-md bg-white shadow-lg"
        >
            <ul
                @keydown.enter.stop.prevent="onOptionSelect()"
                @keydown.space.stop.prevent="onOptionSelect()"
                @keydown.escape="onEscape()"
                @keydown.arrow-up.prevent="onArrowUp()"
                @keydown.arrow-down.prevent="onArrowDown()"
                x-ref="listbox"
                tabindex="-1"
                role="listbox"
                aria-labelledby="listbox-label" :aria-activedescendant="activeDescendant"
                class="max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-y-auto custom-scrollbar focus:outline-none sm:text-sm"
                x-max="1"
            >

                <template x-for="(selectItem, index) in selectItems" :key="selectItem.key">
                    <li
                        x-state:on="Highlighted"
                        x-state:off="Not Highlighted"
                        x-bind:id="'listbox-item-' + index"
                        role="option"
                        @click="choose(selectItem.value)"
                        @mouseenter="chooseByIndex(index)"
                        :class="{ 'text-white bg-indigo-600': selected === index, 'text-gray-900': !(selected === index) }"
                        class="text-gray-900 text-left cursor-default select-none relative py-2 pl-3 pr-4"
                    >
                        <span
                            x-state:on="Selected" x-state:off="Not Selected"
                            :class="{ 'font-semibold': value === index, 'font-normal': !(value === index) }"
                            class="font-normal block truncate"
                            x-text="selectItem.text"
                        ></span>

                        <span
                            x-state:on="Highlighted"
                            x-state:off="Not Highlighted"
                            x-show="value === index"
                            :class="{ 'text-white': selected === index, 'text-indigo-600': !(selected === index) }"
                            class="absolute inset-y-0 left-0 flex items-center pl-1.5 text-indigo-600"
                            style="display: none;"
                        >
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
