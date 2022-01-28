<div>
    <div
        wire:ignore
        id="flash-comp-message"
        x-data="{notificationOpen: true}"
        x-show="notificationOpen"
        x-init="setTimeout(function() { notificationOpen = false }, 4000)"
        class="fixed right-4 top-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="rounded-lg shadow-xs overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if($type == 'success')
                            <!-- success -->
                            <x-heroicon-o-check-circle class="h-5 w-5 text-green-400" />
                        @elseif($type == 'error')
                            <!-- error -->
                            <x-heroicon-o-x-circle class="h-5 w-5 text-red-400" />
                        @else
                            <!-- info -->
                            <x-heroicon-o-exclamation class="h-5 w-5 text-yellow-400" />
                        @endif
                    </div>

                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm leading-5 text-gray-500">
                            {{ $slot }}
                        </p>
                    </div>

                    <div class="ml-4 flex-shrink-0 flex">
                        <button x-on:click.prevent="notificationOpen = false"
                            class="inline-flex text-gray-400 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150">
                            <x-heroicon-o-x class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
