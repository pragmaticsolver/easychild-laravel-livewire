<div
    x-data="{
        enabled: @entangle('enabled').defer,
    }"
>
    @if ($switchVisible)
        <a
            href="#"
            class="block mt-2 md:mt-0 font-medium md:font-normal rounded-md md:rounded-none px-3 md:px-4 py-2 text-sm text-white hover:bg-gray-700 md:text-gray-700 md:hover:bg-gray-100"
            x-on:click.prevent="enabled = true"
        >
            {{ trans('users.switch-child.title') }}
        </a>
    @endif

    <div
        class="fixed z-10 bottom-0 inset-x-0 px-4 pb-6 sm:inset-0 sm:p-0 sm:flex sm:items-center sm:justify-center"
        x-show="enabled"
    >
        <div
            x-show="enabled"
            class="fixed inset-0 transition-opacity"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click.prevent="enabled = false"
        >
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div
            x-show="enabled"
            class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-sm sm:w-full sm:p-6"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
            <div>
                <h3 class="text-sm leading-5 font-medium text-gray-700 mb-3">
                    {{ trans('users.switch-child.title') }}
                </h3>

                <select wire:model.defer="child" class="form-select select-field">
                    @foreach($childrens as $child)
                        <option value="{{ $child->id }}">{{ $child->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-5 sm:mt-6">
                <span class="flex justify-end -mx-2">
                    <div class="px-2">
                        <button type="button"
                            x-on:click.prevent="enabled = false"
                            class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-red-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-indigo transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        >
                            {{ trans('users.switch-child.modal_cancel') }}
                        </button>
                    </div>

                    <div class="px-2">
                        <button type="button"
                            wire:click.prevent="switchChild"
                            class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-indigo-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        >
                            {{ trans('users.switch-child.modal_submit') }}
                        </button>
                    </div>
                </span>
            </div>
        </div>
    </div>
</div>
