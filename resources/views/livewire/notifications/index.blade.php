<div
    class="shadow-lg px-4 py-2 rounded-b-md absolute z-20 top-full left-0 right-0 bg-white text-black sm:max-w-lg sm:w-full sm:right-4 sm:left-auto"
    x-data="{
        dropVisible: false,
    }"
    x-show.transition="dropVisible"
    x-on:toggle-user-notification-drop.window="dropVisible = !dropVisible"
    style="display: none;"
    x-on:click.away="dropVisible = false"
    x-init="$watch('dropVisible', value => value && @this.call('$refresh'));$dispatch('update-unread-notifications-count', {{ $unreadNotificationsCount }})"
    x-on:approve-schedule.window="@this.call('approveSchedule', $event.detail)"
    x-on:reject-schedule.window="@this.call('rejectSchedule', $event.detail)"
>
    <div class="mb-2">
        <nav class="flex space-x-1.5 mb-2" aria-label="Tabs">
            @foreach($this->availableFilters as $filter)
                <button
                    class="
                        text-xs px-2 py-0.5 sm:py-1 font-medium rounded-md text-white outline-none focus:outline-none hover:outline-none
                        {{ $filters[$filter] ? ' bg-indigo-600' : ' bg-gray-600' }}
                    "
                    wire:click.prevent="$toggle('filters.{{ $filter }}')" wire:mdel="filters"
                >
                    {{ trans('notifications.filters-type.' . $filter) }}
                </button>
            @endforeach
        </nav>

        @if ($unreadNotificationsCount || $notifications->count())
            <div class="text-sm text-right">
                <a href="#" wire:click.prevent="markAllNotificationsAsRead">
                    {{ trans('notifications.labels.mark-read') }}
                </a>

                <a class="ml-2" href="#" wire:click.prevent="removeReadNotifications">
                    {{ trans('notifications.labels.remove-read') }}
                </a>
            </div>
        @endif
    </div>

    <div class="max-h-notificationdrop overflow-y-auto custom-scrollbar">
        @forelse($notifications as $notification)
            <div
                class="px-1 pt-1.5 pb-1 {{ $loop->first ? '' : ' mt-1.5 border-t border-black' }}"
                wire:key="{{ $notification->id }}"
            >
                <x-dynamic-component
                    :component="$notification->blade_component"
                    :notification="$notification"
                />
            </div>
        @empty
            <div wire:key="empty-notifications">
                {{ trans('notifications.empty-notification') }}
            </div>
        @endforelse
    </div>
</div>

