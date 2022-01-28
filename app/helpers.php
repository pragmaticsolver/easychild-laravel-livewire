<?php

use App\CustomNotification\DatabaseChannel;
use App\CustomNotification\SmsChannel;
use App\Models\User;
use Barryvdh\Debugbar\Facade as DebugBar;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\WebPushChannel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

if (! function_exists('notificationVia')) {
    function notificationVia(User $user, $mail = false, $database = false, $push = false, $sms = false, $forceMail = false)
    {
        $settings = $user->settings;

        $arr = [];

        if ($database) {
            $arr[] = DatabaseChannel::class;
        }

        if ($push && $user->pushSubscriptions()->count()) {
            $arr[] = WebPushChannel::class;
        }

        // send email if user has opted in for mail
        if ($mail && $user->email && ! $user->isPrincipal()) {
            $mail = $settings && Arr::has($settings, 'mail') ? $settings['mail'] : false;

            if ($mail || $forceMail) {
                $arr[] = 'mail';
            }
        }

        if ($user->isParent() && $sms) {
            $smsEnabled = $settings && Arr::has($settings, 'sms') && $settings['sms'];
            $hasPhoneNo = $settings && Arr::has($settings, 'phone') && $settings['phone'];

            if ($hasPhoneNo && $smsEnabled) {
                $arr[] = SmsChannel::class;
            }
        }

        return $arr;
    }
}

if (! function_exists('langHas')) {
    function langHas($key)
    {
        $default = 'en';

        if (! Lang::has($key) || (Lang::get($key) == Lang::get($key, [], $default))) {
            return false;
        }

        return true;
    }
}

if (! function_exists('generateUserName')) {
    function generateUserName($given_names, $last_name)
    {
        $username = implode('.', [$given_names, $last_name]);

        $germanReplaceMap = [
            'ä' => 'ae',
            'Ä' => 'Ae',
            'ü' => 'ue',
            'Ü' => 'Ue',
            'ö' => 'oe',
            'Ö' => 'Oe',
            'ß' => 'ss',
        ];

        $username = str_replace(array_keys($germanReplaceMap), $germanReplaceMap, $username);

        $username = (string) Str::of($username)->lower()->replace(' ', '-');

        $returnUserName = $username;

        $index = User::where('username', 'like', $returnUserName.'%')->count();
        if ($index) {
            do {
                $returnUserName = $username.$index;
                $index++;
            } while (User::where('username', $returnUserName)->first());
        }

        return $returnUserName;
    }
}

if (! function_exists('numformat')) {
    function numformat($number, $decimals = 0, $dec_point = '.', $thousands_sep = ',')
    {
        if (config('app.locale') == 'de') {
            $dec_point = ',';
            $thousands_sep = '.';
        }

        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
}

if (! function_exists('overtime_show')) {
    function overtime_show($number)
    {
        $returnNum = ($number > 0 ? '-' : '+').numformat(abs($number), 2);

        if (Str::endsWith($returnNum, '0')) {
            $returnNum = Str::of($returnNum)->replaceLast('0', '');
        }

        return $returnNum;
    }
}

if (! function_exists('getManualPdf')) {
    function getManualPdf($user)
    {
        $userType = Str::lower($user->role);

        $fileForUser = [
            'admin' => 'easychild-handbuch-leiter.pdf',
            'manager' => 'easychild-handbuch-leiter.pdf',
            'parent' => 'easychild-handbuch-eltern.pdf',
            'principal' => 'easychild-handbuch-erzieher.pdf',
            'vendor' => 'easychild-handbuch-eltern.pdf',
            'user' => 'easychild-handbuch-eltern.pdf',
        ][$userType];

        return resource_path("pdfs/{$fileForUser}");
    }
}

if (! function_exists('get_push_notification_icon')) {
    function get_push_notification_icon()
    {
        return asset('android-icon-512x512.png');
    }
}

if (! function_exists('getQrCodeForLoginLink')) {
    function getQrCodeForLoginLink(User $user, $text = null)
    {
        if (! $text) {
            $text = route('login', ['token' => $user->token]);
        }

        $fileName = "{$user->uuid}.png";
        $qrCodeFilename = storage_path("app/qrcodes/{$fileName}");

        Storage::disk('qrcodes')->put($fileName, '');

        $qrCode = QrCode::size(200)
            ->format('png')
            ->errorCorrection('H')
            ->generate($text, $qrCodeFilename);

        return $qrCodeFilename;
    }
}

if (! function_exists('langTrans')) {
    function langTrans($key, $replace = [])
    {
        if (config('app.debug')) {
            if (! langHas($key)) {
                DebugBar::error($key);
            }
        }

        // if (config('app.env') == 'testing') {
        //     if (! langHas($key)) {
        //         var_dump($key);
        //     }
        // }

        return Lang::get($key, $replace);
    }
}

if (! function_exists('getColumnFilters')) {
    function getColumnFilters($type = null, $includeFilterDetail = false)
    {
        if (! $type || $type == 'text') {
            $filters[] = [
                'text' => trans('extras.filter.starts_with'),
                'id' => 2,
                'filter' => 'LIKE%',
                'like' => true,
            ];

            $filters[] = [
                'text' => trans('extras.filter.ends_with'),
                'id' => 3,
                'filter' => '%LIKE',
                'like' => true,
            ];

            $filters[] = [
                'text' => trans('extras.filter.contains'),
                'id' => 4,
                'filter' => '%LIKE%',
                'like' => true,
            ];

            $filters[] = [
                'text' => trans('extras.filter.does_not_contains'),
                'id' => 5,
                'filter' => '%LIKE%',
                'like' => true,
                'not' => true,
            ];
        }

        if (! $type || in_array($type, ['text', 'number'])) {
            $filters[] = [
                'text' => trans('extras.filter.equals'),
                'id' => 6,
                'filter' => '=',
            ];

            $filters[] = [
                'text' => trans('extras.filter.does_not_equals'),
                'id' => 7,
                'filter' => '!=',
            ];
        }

        if (! $type || $type == 'number') {
            $filters[] = [
                'text' => trans('extras.filter.greater_than'),
                'id' => 8,
                'filter' => '>',
            ];

            $filters[] = [
                'text' => trans('extras.filter.less_than'),
                'id' => 9,
                'filter' => '<',
            ];

            $filters[] = [
                'text' => trans('extras.filter.greater_than_or_equal_to'),
                'id' => 10,
                'filter' => '>=',
            ];

            $filters[] = [
                'text' => trans('extras.filter.less_than_or_equal_to'),
                'id' => 11,
                'filter' => '<=',
            ];
        }

        if (! $type || $type == 'boolean') {
            $filters[] = [
                'text' => trans('extras.filter.is_true'),
                'id' => 12,
                'filter' => '=',
                'bool' => true,
            ];

            $filters[] = [
                'text' => trans('extras.filter.is_false'),
                'id' => 13,
                'filter' => '=',
                'bool' => false,
            ];
        }

        if (! $type || $type == 'role') {
            if (auth()->user()->isAdmin()) {
                $filters[] = [
                    'text' => trans('extras.role_admin'),
                    'id' => 14,
                    'filter' => '=',
                    'bool' => 'admin',
                ];
            }

            $filters[] = [
                'text' => trans('extras.role_manager'),
                'id' => 15,
                'filter' => '=',
                'bool' => 'manager',
            ];
            $filters[] = [
                'text' => trans('extras.role_parent'),
                'id' => 16,
                'filter' => '=',
                'bool' => 'parent',
            ];
            $filters[] = [
                'text' => trans('extras.role_principal'),
                'id' => 17,
                'filter' => '=',
                'bool' => 'principal',
            ];
            $filters[] = [
                'text' => trans('extras.role_user'),
                'id' => 18,
                'filter' => '=',
                'bool' => 'user',
            ];
            $filters[] = [
                'text' => trans('extras.role_vendor'),
                'id' => 19,
                'filter' => '=',
                'bool' => 'vendor',
            ];
        }

        if (! $type || $type == 'photo_permission') {

            $filters[] = [
                'text' => trans('extras.photo_permission_granted'),
                'id' => 20,
                'filter' => '=',
                'bool' => true,
            ];

            $filters[] = [
                'text' => trans('extras.photo_permission_disabled'),
                'id' => 21,
                'filter' => '=',
                'bool' => false,
            ];
        }

        if ($includeFilterDetail) {
            return $filters;
        }

        $newFilters = [];
        foreach ($filters as $item) {
            $newFilters[] = collect($item)->only(['text', 'id'])->all();
        }

        return $newFilters;
    }
}

if (! function_exists('getTimePickerValues')) {
    function getTimePickerValues($start, $end, $minInterval = 15, $rawValues = false)
    {
        $startTime = now()->setTimeFromTimeString($start);

        $possibleTime = $startTime->copy()->setMinutes(0)->setSeconds(0);
        $finalStartTime = $startTime->copy();

        while ($startTime > $possibleTime) {
            $possibleTime->addMinutes($minInterval);
            $finalStartTime = $possibleTime->copy();
        }

        $startTime = $finalStartTime->copy();
        $endTime = now()->setTimeFromTimeString($end);

        $selectTimes = [];

        while ($startTime <= $endTime) {
            $selectTimes[] = [
                'key' => $startTime->format('H-i-s'),
                'value' => $startTime->format('H:i:s'),
                'text' => $startTime->format('H:i'),
            ];

            $startTime->addMinutes($minInterval);
        }

        if ($rawValues) {
            return collect($selectTimes)->pluck('value')->all();
        }

        return $selectTimes;
    }
}

if (! function_exists('BKgetTimePickerValues')) {
    function BKgetTimePickerValues($start, $end, $minInterval = 30)
    {
        $parsedStart = Str::of($start)->explode(':');
        $finalStart = intval($parsedStart->first());

        $startAtZero = true;
        if ($parsedStart->last() != '00') {
            $startAtZero = false;
        }

        $parsedEnd = Str::of($end)->explode(':');
        $finalEnd = intval($parsedEnd->first());

        $endAtZero = true;
        if ($parsedEnd->last() != '00') {
            $endAtZero = false;
        }

        $selectTimes = [];
        for ($x = $finalStart; $x <= $finalEnd; $x++) {
            $time = $x;

            if ($time < 10) {
                $time = '0'.$time;
            }

            if ($x == $finalStart) {
                if ($startAtZero) {
                    $selectTimes[] = $time.':'.'00:00';
                    $selectTimes[] = $time.':'.'15:00';
                    $selectTimes[] = $time.':'.'30:00';
                    $selectTimes[] = $time.':'.'45:00';
                } else {
                    $selectTimes[] = $time.':'.'30:00';
                    $selectTimes[] = $time.':'.'45:00';
                }
            } elseif ($x == $finalEnd) {
                if ($endAtZero) {
                    $selectTimes[] = $time.':'.'00:00';
                } else {
                    $selectTimes[] = $time.':'.'00:00';
                    // $selectTimes[] = $time . ':' . '15:00';
                    $selectTimes[] = $time.':'.'30:00';
                    // $selectTimes[] = $time . ':' . '45:00';
                }
            } else {
                $selectTimes[] = $time.':'.'00:00';
                $selectTimes[] = $time.':'.'15:00';
                $selectTimes[] = $time.':'.'30:00';
                $selectTimes[] = $time.':'.'45:00';
            }
        }

        return $selectTimes;
    }
}
