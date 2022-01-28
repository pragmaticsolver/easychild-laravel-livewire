<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class UserEventsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $title;
    public $body;
    public $url;
    public $pushTitle;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $body, $url, $pushTitle)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->pushTitle = $pushTitle;

        if (! $this->url) {
            $this->url = route('dashboard');
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return notificationVia($notifiable, false, false, true);
    }

    public function toWebPush($notifiable, $notification)
    {
        $body = $this->body;
        if (is_array($this->body)) {
            $body = $this->body['text1'];
            $body .= $this->body['text2'];
        }

        return (new WebPushMessage)
            ->title($this->title)
            ->icon(get_push_notification_icon())
            ->body($body)
            ->action(trans('messages.notifications.view'), $this->url)
            ->data([
                'type' => $this->pushTitle,
                'id' => $notification->id,
                'body' => $this->body,
            ]);
    }
}
