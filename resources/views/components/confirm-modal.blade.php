@props([
    'confirmId' => ''
])

@php
    $formData = [
        'form' => [
            'enabled' => false,
            'title' => '',
            'description' => '',
            'event' => null,
            'cancelEvent' => null,
            'cancelText' => trans('components.confirm_modal.cancel_btn_text'),
            'confirmText' => trans('components.confirm_modal.confirm_btn_text'),
            'uuid' => null,
        ],
    ];
@endphp

<div
    wire:ignore
    class="fixed z-30 bottom-0 inset-x-0 px-4 pb-6 sm:inset-0 sm:p-0 sm:flex sm:items-center sm:justify-center"
    x-show="form.enabled"
    x-data="{{ json_encode($formData) }}"
    :class="{'invisible': !form.enabled}"
    x-on:{{ $confirmId }}-confirm-modal-open.window="
        form.title = $event.detail.title;
        form.description = $event.detail.description;
        form.uuid = $event.detail.uuid;
        form.event = $event.detail.event;
        form.cancelEvent = $event.detail.cancelEvent;

        if ($event.detail.cancelText) form.cancelText = $event.detail.cancelText;
        if ($event.detail.confirmText) form.confirmText = $event.detail.confirmText;

        form.enabled = true;
    "
>
    <div
        x-show="form.enabled"
        class="fixed inset-0 transition-opacity"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.prevent="form.enabled = false"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="form.enabled"
        class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform transition-all sm:max-w-sm sm:w-full sm:p-6"
        role="dialog" aria-modal="true" aria-labelledby="modal-headline"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        style="display: none;"
    >
        <div>
            <h3 class="text-base leading-5 font-bold text-gray-700 mb-3" x-text="form.title"></h3>

            <p class="text-sm leading-5 text-gray-600 m-0" x-text="form.description"></p>
        </div>

        <div class="mt-5 sm:mt-6">
            <span class="flex justify-end -mx-2">
                <div class="px-2">
                    <button type="button"
                        x-on:click.prevent="form.enabled = false; form.cancelEvent && $dispatch(form.cancelEvent);"
                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-red-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-indigo transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        x-text="form.cancelText"
                    ></button>
                </div>

                <div class="px-2">
                    <button type="button"
                        x-on:click.prevent="form.enabled = false; form.event && $dispatch(form.event, form.uuid);"
                        class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-indigo-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        x-text="form.confirmText"
                    ></button>
                </div>
            </span>
        </div>
    </div>
</div>
