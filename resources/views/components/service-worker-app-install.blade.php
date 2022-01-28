@php
    $messages = collect([
        'app-installed' => trans('extras.service-worker.app-installed'),
        'install-declined' => trans('extras.service-worker.install-declined'),
        'install-later-msg' => trans('extras.service-worker.install-later-msg'),
    ]);
@endphp

<div
    x-data="serviceWorkerAppInstall()"
    x-init="onMounted({{ $messages }})"
>
    <div
        x-show="barDisabled != null && !barDisabled && (needsInstall && deferredPrompt)"
        class="bg-indigo-500 p-4 fixed z-50 bottom-0 left-0 right-0 transform"
        x-transition:enter="transition ease-in duration-300"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-out duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-full"
        style="display: none;"
    >
        <table class="w-full">
            <tr>
                <td rowspan="2" class="w-16 px-4">
                    <img class="w-full" src="{{ asset('img/easychild.svg') }}" alt="{{ config('app.name') }}" />
                </td>

                <td rowspan="2" class="text-sm leading-5 text-white">
                    <h4 class="text-md">
                        {{ trans('extras.service-worker.thank-you', ['name' => config('app.name')]) }}
                    </h4>
                    <span>
                        {{ trans('extras.service-worker.thank-you-description', ['name' => config('app.name')]) }}
                    </span>
                </td>

                <td class="w-12">
                    <button type="button" x-on:click.prevent="askForAppInstall" class="text-xs text-white rounded bg-orange-600 hover:text-gray-300 hover:bg-orange-400 focus:outline-none flex px-3 py-1 items-center justify-center hover:cursor-pointer">
                        <x-heroicon-o-download class="w-5 h-5 mr-2"></x-heroicon-o-download>
                        {{ trans('extras.service-worker.install-btn-text') }}
                    </button>
                </td>
            </tr>

            <tr>
                <td class="w-12 pt-2">
                    <button type="button" x-on:click.prevent="installLater" class="rounded text-xs text-white bg-red-600 hover:text-gray-400 focus:outline-none flex px-3 py-1 items-center justify-center hover:cursor-pointer">
                        <x-heroicon-o-hand class="w-5 h-5 mr-2"></x-heroicon-o-hand>
                        {{ trans('extras.service-worker.later-btn-text') }}
                    </button>
                </td>
            </tr>
        </table>
    </div>

    <x-confirm-modal confirm-id="schedule-approval"></x-confirm-modal>
</div>
