<?php

return [
    'checkoutCauses' => [
        'ill',
        'vacation',
        'cure',
        'other',
    ],
    'format' => [
        'date' => 'd.m.Y',
        'time' => 'H:i',
        'datetime' => 'd.m.Y H:i',
        'javascript' => [
            'date' => 'd.m.Y',
            'datetime' => 'd.m.Y HH:mm',
        ],
    ],
    'events' => [
        'colors' => [
            'gray',
            'red',
            'yellow',
            'green',
            'blue',
            'indigo',
            'purple',
            'pink',
        ],
    ],
    'usersTypes' => [
        'Admin',
        'Principal',
        'User',
    ],
    'perPage' => env('MODEL_PER_PAGE', 15),
    'defaultOpeningTimes' => [
        'start' => '06:00:00',
        'end' => '18:00:00',
    ],
    'userAvailabilityOptions' => [
        [
            'value' => 'available',
            'translation' => 'extras.user-availability.available',
        ],
        [
            'value' => 'not-available',
            'translation' => 'extras.user-availability.not-available',
        ],
        [
            'value' => 'not-available-with-time',
            'translation' => 'extras.user-availability.not-available-with-time',
        ],
    ],
    'organizationScheduleSettings' => [
        'eats_onsite' => [
            'breakfast' => env('ORG_USER_EATS_ONSITE_BREAKFAST', true),
            'lunch' => env('ORG_USER_EATS_ONSITE_LUNCH', true),
            'dinner' => env('ORG_USER_EATS_ONSITE_DINNER', true),
        ],
        'availability' => env('ORG_USER_AVAILABILITY', 'available'),
    ],
    'minLeadDays' => env('MIN_LEAD_DAYS', 1),
    'minSelectionDays' => env('MIN_SELECTION_DAYS', 1),
    'messages' => [
        'managersAbility' => [
            [
                'id' => 1,
                'text' => 'messages.room_type.single_manager',
                'role_filter' => 'Manager',
                'group_filter' => 'organization',
                'single' => true,
            ],
            [
                'id' => 2,
                'text' => 'messages.room_type.group_single_principal',
                'role_filter' => 'Principal',
                'group_filter' => 'group',
                'single' => true,
            ],
            [
                'id' => 3,
                'text' => 'messages.room_type.group_single_user',
                'role_filter' => 'User',
                'group_filter' => 'group',
                'single' => true,
            ],
        ],
        'principalsAbility' => [
            [
                'id' => 1,
                'text' => 'messages.room_type.single_manager',
                'role_filter' => 'Manager',
                'group_filter' => 'organization',
                'single' => true,
            ],
            [
                'id' => 2,
                'text' => 'messages.room_type.group_single_principal',
                'role_filter' => 'Principal',
                'group_filter' => 'group',
                'single' => true,
            ],
            [
                'id' => 3,
                'text' => 'messages.room_type.group_single_user',
                'role_filter' => 'User',
                'group_filter' => 'group',
                'single' => true,
            ],
        ],
        'usersAbility' => [
            [
                'id' => 1,
                'text' => 'messages.room_type.your_principal',
                'role_filter' => 'User',
                'group_filter' => 'group',
                'single' => true,
            ],
        ],
    ],
    'auth' => [
        'passwordLength' => 25,
        'tokenLength' => 99,
    ],
];
