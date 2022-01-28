<?php

namespace App\Notifications;

use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class CalendarEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public CalendarEvent $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(CalendarEvent $event, $data = [])
    {
        $this->event = $event;
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
        $url = route('calendar', [
            'date' => $this->event->from->format('Y-m-d'),
        ]);

        return (new MailMessage)
            ->subject(trans('calendar-events.notifications.subject'))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]))
            ->line($this->event->title)
            ->line($this->event->description)
            ->action(trans('calendar-events.notifications.view-event'), $url);
    }

    public function toWebPush($notifiable, $notification)
    {
        $url = route('calendar', [
            'date' => $this->event->from->format('Y-m-d'),
        ]);

        return (new WebPushMessage)
            ->title(trans('calendar-events.notifications.subject'))
            ->icon(get_push_notification_icon())
            ->body($this->event->title)
            ->action(trans('calendar-events.notifications.view-event'), $url)
            ->data([
                'type' => 'calendar-event',
                'id' => $notification->id,
                'body' => $this->event->title,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toLxSms($notifiable)
    {
        $body = $this->event->organization->name;
        $body .= PHP_EOL;
        $body .= trans('calendar-events.notifications.subject');
        $body .= PHP_EOL;
        $body .= $this->event->title;

        return [
            'text' => $body,
        ];
    }
}
