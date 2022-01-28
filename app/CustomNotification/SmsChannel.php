<?php

namespace App\CustomNotification;

use App\Models\SmsLog;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toLxSms($notifiable);

        if (! Arr::has($message, 'text')) {
            return;
        }

        $message['sender_id'] = config('services.sms.sender_id');
        $message['service_code'] = 'direct';

        $userSettings = $notifiable->settings;

        $message['phone'] = '+49'.$userSettings['phone'];

        $lang = config('app.locale');
        if (Arr::has($userSettings, 'lang')) {
            $lang = $userSettings['lang'];
        }

        $message['voice_lang'] = Str::upper($lang);

        $token = config('services.sms.secret');

        $response = Http::withHeaders([
            "X-LOX24-AUTH-TOKEN" => $token,
        ])->post(config('services.sms.api_url'), $message);

        SmsLog::create([
            'user_id' => $notifiable->id,
            'success' => $response->successful(),
            'code' => $response->status(),
            'data' => $response->json(),
        ]);
    }
}
