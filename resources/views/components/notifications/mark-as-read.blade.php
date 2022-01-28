@props([
    'notification',
])

<div class="mt-2 flex items-center justify-between">
    <div class="text-sm {{ $notification->read_at ? '' : ' font-bold text-indigo-600' }}">
        {{ $notification->created_at->diffForHumans() }}
    </div>

    @if (is_null($notification->read_at))
        <button wire:click="markAsRead('{{ $notification->id }}')" class="ml-4 outline-none focus:outline-none w-3 h-3 rounded-full bg-indigo-600 hover:bg-indigo-500" title="{{ trans('notifications.mark-read') }}"></button>
    @endif
</div>
