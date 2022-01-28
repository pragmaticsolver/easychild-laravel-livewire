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
                @if ($notification->related->status == 'approved')
                    {{
                        trans('schedules.notification.user_schedule_approved_msg_for_parent', [
                            'child' => $notification->data['target_child'],
                            'name' => $notification->data['target_user'],
                        ])
                    }}
                @else
                    {{
                        trans('schedules.notification.user_schedule_rejected_msg_for_parent', [
                            'child' => $notification->data['target_child'],
                            'name' => $notification->data['target_user'],
                        ])
                    }}
                @endif

                <div class="text-sm">
                    {{ $notification->related->approval_description }}
                </div>
            </div>
        @endif
    </div>

    <x-notifications.mark-as-read :notification="$notification" />
</div>
