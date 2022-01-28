<div
    class="fixed z-10 p-4 inset-0 flex items-center justify-center invisible"
    x-data
    wire:offline.class.remove="invisible"
>
    <div
        class="fixed inset-0 transition-opacity"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
    </div>

    <div
        class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full sm:p-6"
        role="dialog" aria-modal="true" aria-labelledby="modal-headline"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div>
            <div class="text-black text-3xl md:text-5xl mb-5 font-black">
                @yield('code', trans('errors.offline_title'))
            </div>

            <p class="text-gray-700 text-xl md:text-2xl font-light leading-normal">
                @yield('message', trans('errors.offline_message'))
            </p>
        </div>
    </div>
</div>
