<div
    class="relative"
    x-data="{
        dropVisible: @entangle('dropVisible').defer
    }"
>
    <div class="flex items-center mb-3">
        <div class="text-gray-600 text-sm">{{ trans('components.multi-select.description') }}</div>

        <div class="ml-3 relative inline-block text-left" x-on:click.away="dropVisible = false">
            <div>
                <span class="rounded-md shadow-sm">
                    <button x-on:click.prevent="dropVisible = !dropVisible" type="button" class="rounded-full inline-flex justify-center w-full border border-gray-300 p-2 bg-white text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-100 active:text-gray-800 transition ease-in-out duration-150">
                        <x-heroicon-o-plus class="h-4 w-4" />
                    </button>
                </span>
            </div>

            <div
                x-show="dropVisible"
                class="origin-top-left absolute right-0 mt-2 w-56 rounded-md shadow-lg"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="eatransition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
            >
                <div class="rounded-md bg-white shadow-xs max-h-56 overflow-x-hidden overflow-y-auto custom-scrollbar">
                    <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        @foreach ($items as $item)
                            <button type="button" wire:key="{{ $item['uuid'] }}" wire:click="addSelect('{{ $item['uuid'] }}')" class="block w-full px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900">
                                {{ $item[$displayKey] }}
                            </button>
                        @endforeach
                        @if (count($items) === 0)
                            <div class="block px-4 py-2 text-sm leading-5 text-gray-700 focus:outline-none">{{ trans('extras.no_item_found') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="-mx-1">
        @foreach ($selected as $item)
            <span wire:key="{{ $item['uuid'] }}" class="inline-flex items-center py-2 px-4 mx-1 my-1 rounded-full text-sm font-medium leading-4 bg-indigo-100 text-indigo-800">
                {{ $item[$displayKey] }}
                <button type="button" class="flex-shrink-0 ml-2 inline-flex text-indigo-500 focus:outline-none focus:text-indigo-700" wire:click="removeSelect('{{ $item['uuid'] }}')">
                    <x-heroicon-o-x class="h-4 w-4" />
                </button>
            </span>
        @endforeach
    </div>
</div>
