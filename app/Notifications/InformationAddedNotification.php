<?php

namespace App\Notifications;

use App\Models\Information;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class InformationAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $information;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Information $information)
    {
        $this->information = $information;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return notificationVia($notifiable, true, true, true, true);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(trans('informations.notification.new_information_subject'))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]))
            ->line(trans('informations.notification.new_information_added', ['title' => $this->information->title]))
            ->action(trans('informations.notification.action_link_text'), route('informations.index'));
    }

    public function toDatabase()
    {
        return [
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title(trans('informations.notification.new_information_subject'))
            ->icon(get_push_notification_icon())
            ->body(trans('informations.notification.new_information_added', ['title' => $this->information->title]))
            ->action(trans('informations.notification.action_link_text'), route('informations.index'))
            ->data([
                'type' => 'information-added',
                'id' => $notification->id,
            ]);
    }

    public function toLxSms($notifiable)
    {
        $body = $this->information->organization->name;
        $body .= PHP_EOL;
        $body .= trans('informations.notification.new_information_added', ['title' => $this->information->title]);

        return [
            'text' => $body,
        ];
    }
}
