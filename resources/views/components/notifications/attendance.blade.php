@props([
    'notification',
])

<div>
    <div class="flex items-center mb-2">
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
                @if ($notification->related->status == 'pending')
                    {{ trans('schedules.notification.title', ['name' => $notification->data['target_user']]) }}
                @else
                    {{ $notification->related->dealtTitle($notification->data['target_user']) }}
                @endif

                <div class="text-sm">
                    {{ $notification->related->approval_description }}
                </div>
            </div>
        @endif
    </div>

    @if ($notification->related && is_null($notification->related->last_dealt_at))
        <div class="flex justify-end space-x-4 items-center" x-data>
            <button
                type="button"
                x-on:click.prevent="$dispatch('schedule-approval-confirm-modal-open', {
                    'title': '{{ trans("schedules.notification.confirm_title") }}',
                    'description': '{{ trans("schedules.notification.confirm_reject_description") }}',
                    'cancelText': '{{ trans("schedules.notification.confirm_cancel_btn_text") }}',
                    'confirmText': '{{ trans("schedules.notification.confirm_reject_btn_text") }}',
                    'event': 'reject-schedule',
                    'uuid': '{{ $notification->related->uuid }}',
                })"
                class="inline-flex items-center py-1.5 px-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-gray-600 hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:shadow-outline-gray active:bg-gray-700 transition ease-in-out duration-150"
            >
                {{ trans('schedules.notification.reject_text') }}
            </button>

            <button
                type="button"
                x-on:click.prevent="$dispatch('schedule-approval-confirm-modal-open', {
                    'title': '{{ trans("schedules.notification.confirm_title") }}',
                    'description': '{{ trans("schedules.notification.confirm_approve_description") }}',
                    'cancelText': '{{ trans("schedules.notification.confirm_cancel_btn_text") }}',
                    'confirmText': '{{ trans("schedules.notification.confirm_approve_btn_text") }}',
                    'event': 'approve-schedule',
                    'uuid': '{{ $notification->related->uuid }}',
                })"
                class="inline-flex items-center py-1.5 px-2 border border-transparent text-xs leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150"
            >
                {{ trans('schedules.notification.approve_text') }}
            </button>
        </div>
    @endif

    <x-notifications.mark-as-read :notification="$notification" />
</div>
