@props([
    'notification',
])

<div>
    <div class="flex items-center">
        <div class="w-10 mr-3.5">
            <span
                x-data="{
                    avatar: '{{ $notification->data['target_avatar'] ?? '' }}'
                }"
            >
                <img x-show="avatar" :src="avatar" alt="{{ $notification->data['target_user'] }}" class="w-10 h-10 rounded-full" src="" style="display: none;">

                <svg x-show="! avatar" class="h-10 w-10 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </span>
        </div>

        @if ($notification->related)
            <div class="flex-grow">
                {{ trans('notes.notification.new_note_added', ['name' => $notification->data['target_user'], 'title' => $notification->related->title]) }}
            </div>
        @endif
    </div>

    <div class="flex justify-end space-x-4 items-center">
        <button
            wire:click.prevent="navigateToRelatedResource('{{ $notification->id }}')"
            class="inline-flex items-center py-1.5 px-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
        >
            {{ trans('notes.notification.action_link_text') }}
        </button>
    </div>

    <x-notifications.mark-as-read :notification="$notification" />
</div>
