@props([
    'sortEnabled' => false,
    'sortBy',
    'name',
    'order' => 'ASC',
    'text',
    'justifyType' => 'left',
    'filtersList' => [],
    'dropToLeft' => false,
    'wireKey' => null,
    'visible' => true,
    'isBooleanType' => false,
])

@php
    $justifyClass = [
        'left' => 'justify-start',
        'center' => 'justify-center',
        'right' => 'justify-end'
    ][$justifyType];
@endphp

<td {{ $attributes->merge(['class' => '']) }}>
    <div class="flex items-center {{ $justifyClass }}">
        <div wire:ignore>
            @if ($visible)
                <div
                    class="pr-3 relative z-10"
                    x-data="{
                        filterOpen: false,
                        filter: null,
                        query: '',
                        dropToLeft: @json($dropToLeft),
                        offset: 0,
                        isBooleanType: @json($isBooleanType),
                        calculateLeftOrRightOffset() {
                            var offsetLeft = $el.offsetLeft;
                            var width = $el.offsetWidth;

                            if (this.dropToLeft) {
                                this.offset = window.innerWidth - (offsetLeft + width);
                                this.$refs.mainDrop.style.right = this.offset + 'px'
                            } else {
                                this.offset = offsetLeft;
                                this.$refs.mainDrop.style.left = this.offset + 'px'
                            }
                        },
                    }"
                    x-init="calculateLeftOrRightOffset(); $watch('filterOpen', val => calculateLeftOrRightOffset())"
                    x-on:click.away="filterOpen = false"
                >
                    <a
                        href="#"
                        x-on:click.prevent="filterOpen = !filterOpen"
                        class="outline-none focus:outline-none"
                        :class="{
                            'text-blue-500': isBooleanType ? (!!filter) : filter && query
                        }"
                    >
                        <x-heroicon-o-filter class="w-5 h-5"></x-heroicon-o-filter>
                    </a>

                    <div
                        x-ref="mainDrop"
                        class="fixed mt-2 w-56 rounded-md shadow-lg text-left bg-white ring-1 ring-black ring-opacity-5"
                        :class="{
                            'origin-top-left': dropToLeft,
                            'origin-top-right': !dropToLeft,
                        }"
                        style="display: none;"
                        x-show="filterOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                    >
                        <div x-show="! isBooleanType" class="flex bg-gray-200 p-2 rounded-t-md">
                            <div class="flex-grow">
                                <input x-model="query" placeholder="{{ trans('extras.filter.query') }}" type="text" class="w-full text-black outline-none border border-transparent rounded-l-md h-8 leading-none text-sm px-2 py-1">
                            </div>

                            <a href="#" x-on:click.prevent="filterOpen = false; @this.call('setFilters', '{{ $wireKey }}', filter, query);" class="p-1 w-8 inline-flex items-center justify-center bg-blue-500 rounded-r-md text-white hover:bg-blue-600 outline-none hover:outline-none">
                                <x-heroicon-o-check class="w-4 h-4"></x-heroicon-o-check>
                            </a>
                        </div>

                        <div class="py-1">
                            <a
                                href="#"
                                x-on:click.prevent="filter = null; query = ''; filterOpen = false; @this.call('clearFilter', '{{ $wireKey }}')"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white" role="menuitem"
                            >
                                {{ trans('extras.filter.clear_filter') }}
                            </a>

                            @foreach($filtersList as $filterItem)
                                <a
                                    wire:key="{{ $filterItem['id'] }}"
                                    href="#"
                                    @if ($isBooleanType)
                                        x-on:click.prevent="filter = {{ $filterItem['id'] }}; filterOpen = false; @this.call('setFilters', '{{ $wireKey }}', filter, true);"
                                    @else
                                        x-on:click.prevent="filter = {{ $filterItem['id'] }};"
                                    @endif
                                    class="block px-4 py-2 text-sm hover:bg-blue-500 hover:text-white" role="menuitem"
                                    :class="{
                                        'bg-blue-500 text-white': filter == @json($filterItem['id']),
                                        'text-gray-700': filter != @json($filterItem['id']),
                                    }"
                                >
                                    {{ $filterItem['text'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($sortEnabled)
            <div>
                <x-sort-column
                    :text="$text"
                    :sort-by="$sortBy"
                    :name="$name"
                    :order="$order"
                ></x-sort-column>
            </div>
        @else
            <div>
                {{ $text }}
            </div>
        @endif
    </div>
</td>
