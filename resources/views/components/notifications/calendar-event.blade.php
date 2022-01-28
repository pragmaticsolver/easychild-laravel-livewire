@props([
    'notification',
])

<div>
    <div class="flex items-center">
        <div class="w-10 mr-3.5">
            <x-heroicon-o-calendar class="w-10 h-10" />
        </div>

        @if ($notification->related)
            <div class="flex-grow">
                {{ $notification->related->title }}
            </div>
        @endif
    </div>

    <div class="flex justify-end space-x-4 items-center">
        <button
            wire:click="navigateToRelatedResource('{{ $notification->id }}')"
            class="inline-flex items-center py-1.5 px-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
        >
            <x-heroicon-o-eye class="-ml-0.5 mr-2 h-4 w-4" />

            {{ trans('notifications.view-event') }}
        </button>
    </div>

    <x-notifications.mark-as-read :notification="$notification" />
</div>
