<div
    class="w-full block relative"
>
    <div
        x-data="{
            selectOpen: @entangle('selectOpen').defer,
            search: @entangle('search'),
            selected: @entangle('selected'),
            holderWidth: 0,
            holderTop: 0,
            calculateElPosition() {
                if (this.selectOpen) {
                    this.holderWidth = this.$el.getBoundingClientRect().width + 'px'
                    this.holderTop = (this.$el.getBoundingClientRect().top + this.$el.getBoundingClientRect().height) + 'px'
                }
            }
        }"
        x-on:resize.window="calculateElPosition()"
        x-on:scroll.window="calculateElPosition()"
        x-init="
            $watch('selectOpen', value => calculateElPosition());
            $watch('search', value => calculateElPosition());
            calculateElPosition();
        "
    >
        <div
            class="relative block appearance-none bg-white w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5"
            :class="{'cursor-pointer': !selectOpen}"
            x-on:click.prevent="selectOpen = ! selectOpen"
        >
            @if ($selected)
                {{ $selectedData }}
            @else
                {{ trans('extras.no_item_selected') }}
            @endif

            <template x-if="selectOpen && selected">
                <button x-on:click.prevent="$wire.clearItem" type="button" class="outline-none focus:outline-none absolute right-2 top-1/2 -mt-2.5 text-gray-400">
                    <span>
                        <x-heroicon-o-x class="w-5 h-5"></x-heroicon-o-x>
                    </span>
                </button>
            </template>

            <template x-if="! selectOpen || ! selected">
                <span class="outline-none focus:outline-none absolute right-2 top-1/2 -mt-2.5 text-gray-400">
                    <x-heroicon-o-selector class="w-5 h-5"></x-heroicon-o-selector>
                </span>
            </template>
        </div>

        <div
            x-on:click.away="selectOpen = false"
            x-show="selectOpen"
            x-ref="dropHolder"
            class="fixed z-10 mt-1 bg-white shadow-lg rounded-b-md"
            x-transition:enter="transition ease-in duration-75"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-out duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            style="display: none;"
        >
            <div x-bind:style="`width: ${holderWidth}; top: ${holderTop};`">
                <div class="relative p-2 bg-gray-200 rounded-t-md">
                    <input type="text" x-ref="searchInput" wire:model.debounce.750ms="search" class="text-field" placeholder="{{ trans('extras.search') }}">

                    <div wire:loading.class.remove="opacity-0" class="opacity-0 absolute inset-y-0 right-0 pr-3 leading-none flex items-center pointer-events-none">
                        <x-loading></x-loading>
                    </div>
                </div>

                <ul class="max-h-56 overflow-x-hidden overflow-y-auto custom-scrollbar">
                    @if ($data->count())
                        @foreach($data as $item)
                            <li wire:key="{{ $item['id'] }}">
                                <button
                                    x-bind:class="{
                                        'bg-indigo-400 text-white': @json($item[$provider['key']] === $selected),
                                        'text-gray-700 hover:bg-indigo-400 hover:text-white': @json($item[$provider['key']] !== $selected),
                                        'rounded-b-md': @json($loop->last),
                                    }"
                                    x-on:click.prevent="
                                        @if (! $emitUpWhenUpdated)
                                            $dispatch('input', {{ $item[$provider['key']] }});
                                        @endif
                                        $wire.selectItem({{ $item[$provider['key']] }}).then(function() {
                                            selectOpen = false;
                                        });
                                    "
                                    class="block w-full py-2 px-4 text-left cursor-pointer"
                                    type="button"
                                >
                                    {{ $item[$provider['text']] }}
                                    @if (Arr::has($provider, 'secondaryText'))
                                        @if (is_string($provider['secondaryText']))
                                            <span class="text-xs">({{ $item[$provider['secondaryText']] }})</span>
                                        @else
                                            @php
                                                $value = Str::lower($item[$provider['secondaryText'][0]]);
                                            @endphp
                                            <span class="text-xs">
                                                (
                                                    @if($value === 'admin')
                                                        {{ trans('dashboard.role_admin') }}
                                                    @elseif($value == 'manager')
                                                        {{ trans('dashboard.role_manager') }}
                                                    @elseif($value == 'parent')
                                                        {{ trans('dashboard.role_parent') }}
                                                    @elseif($value == 'principal')
                                                        {{ trans('dashboard.role_principal') }}
                                                    @elseif($value == 'user')
                                                        {{ trans('dashboard.role_user') }}
                                                    @elseif($value == 'vendor')
                                                        {{ trans('dashboard.role_vendor') }}
                                                    @endif
                                                )
                                            </span>
                                        @endif
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    @else
                        <li class="py-2 px-4 cursor-default">
                            {{ trans('extras.no_item_found') }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
