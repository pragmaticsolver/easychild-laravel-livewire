@if(session()->has('error') || session()->has('success') || session()->has('info') || session()->has('message'))
    <div
        id="flash-message"
        x-data="{notificationOpen: true}"
        x-show="notificationOpen"
        class="right-4 top-4 fixed z-50 w-full max-w-sm bg-white rounded-lg shadow-lg pointer-events-auto"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="overflow-hidden rounded-lg shadow-xs">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if(session()->has('success'))
                            <!-- success -->
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-400" />
                        @elseif(session()->has('error'))
                            <!-- error -->
                            <x-heroicon-o-x-circle class="w-5 h-5 text-red-400" />
                        @else
                                <!-- info -->
                            <x-heroicon-o-exclamation class="w-5 h-5 text-yellow-400" />
                        @endif
                    </div>

                    <div class="flex-1 w-0 ml-3">
                        <p class="text-sm leading-5 text-gray-500">
                            @if(session()->has('info'))
                                {{ session('info') }}
                            @elseif(session()->has('success'))
                                {{ session('success') }}
                            @elseif(session()->has('error'))
                                {{ session('error') }}
                            @elseif(session()->has('message'))
                                {{ session('message') }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-shrink-0 ml-4">
                        <button x-on:click.prevent="notificationOpen = false"
                            class="focus:outline-none focus:text-gray-500 inline-flex text-gray-400 transition duration-150 ease-in-out">
                            <x-heroicon-o-x class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    window.addEventListener('turbolinks:load', function() {
        if (window.flashTimeout) {
            clearTimeout(window.flashTimeout)
        }

        window.flashTimeout = setTimeout(function() {
            let elem = document.querySelector('#flash-message');
            if (elem) {
                elem.parentNode.removeChild(elem)
            }
        }, 4000)
    })
</script>
