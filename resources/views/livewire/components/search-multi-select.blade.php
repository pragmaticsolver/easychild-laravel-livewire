<div
    class="relative"
    x-data="{
        dropVisible: @entangle('dropVisible').defer,
        holderTop: 0,
        holderRight: 0,
        calculateElPosition() {
            if (this.dropVisible) {
                this.holderTop = (this.$refs.triggerBtn.getBoundingClientRect().top + this.$refs.triggerBtn.getBoundingClientRect().height) + 'px'
                this.holderRight = ((window.innerWidth - this.$refs.triggerBtn.getBoundingClientRect().left) - this.$refs.triggerBtn.getBoundingClientRect().width) + 'px'
            }
        }
    }"
    x-on:resize.window="calculateElPosition()"
    x-on:scroll.window="calculateElPosition()"
    x-init="
        $watch('dropVisible', value => calculateElPosition());
        calculateElPosition();
    "
>
    <div class="flex items-center mb-3">
        <div class="text-gray-600 text-sm">{{ trans('components.multi-select.description') }}</div>

        <div class="ml-3 relative inline-block text-left" x-on:click.away="dropVisible = false">
            <div>
                <span class="rounded-md shadow-sm" x-ref="triggerBtn">
                    <button x-on:click.prevent="dropVisible = !dropVisible" type="button" class="rounded-full inline-flex justify-center w-full border border-gray-300 p-2 bg-white text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-800 transition ease-in-out duration-150">
                        <x-heroicon-o-plus class="h-4 w-4" />
                    </button>
                </span>
            </div>

            <div
                x-show="dropVisible"
                {{-- x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="eatransition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95" --}}
                style="display: none;"
            >
                <div
                    class="origin-top-left fixed z-20 mt-2 w-56 rounded-md shadow-lg"
                    x-bind:style="`top: ${holderTop}; right: ${holderRight}`"
                >
                    @if ($enableSearch)
                        <div class="relative p-2 bg-gray-200 rounded-t-md">
                            <input type="text" x-ref="searchInput" wire:model.debounce.750ms="search" class="text-field" placeholder="{{ trans('extras.search') }}">

                            <div wire:loading.class.remove="opacity-0" class="opacity-0 absolute inset-y-0 right-0 pr-3 leading-none flex items-center pointer-events-none">
                                <x-loading></x-loading>
                            </div>
                        </div>
                    @endif

                    <div class="{{ $enableSearch ? '' : 'rounded-t-md' }} rounded-bl-md rounded-br-md bg-white shadow-xs max-h-56 overflow-x-hidden overflow-y-auto custom-scrollbar">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                            @foreach ($availableItems as $item)
                                <button
                                    type="button"
                                    wire:key="available-item-{{ $wireKey }}-{{ $item['uuid'] }}"
                                    @if ($modelIdKey == 'id')
                                        wire:click="addSelect({{ $item[$modelIdKey] }})"
                                    @else
                                        wire:click="addSelect('{{ $item[$modelIdKey] }}')"
                                    @endif
                                    class="block w-full px-4 py-2 text-sm text-left leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900"
                                >
                                    {{ $item[$displayKey] }}
                                </button>
                            @endforeach

                            @if (count($availableItems) === 0)
                                <div class="block px-4 py-2 text-sm leading-5 text-gray-700 focus:outline-none">{{ trans('extras.no_item_found') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="-mx-1">
        @foreach ($selectedItems as $item)
            <span wire:key="selected-item-{{ $wireKey }}-{{ $item['uuid'] }}" class="inline-flex items-center py-2 px-4 mx-1 my-1 rounded-full text-sm font-medium leading-4 bg-indigo-100 text-indigo-800">
                {{ $item[$displayKey] }}

                <button
                    type="button"
                    class="flex-shrink-0 ml-2 inline-flex text-indigo-500 focus:outline-none focus:text-indigo-700"
                    @if ($modelIdKey == 'id')
                        wire:click="removeSelect({{ $item[$modelIdKey] }})"
                    @else
                        wire:click="removeSelect('{{ $item[$modelIdKey] }}')"
                    @endif
                >
                    <x-heroicon-o-x class="h-4 w-4" />
                </button>
            </span>
        @endforeach
    </div>
</div>
