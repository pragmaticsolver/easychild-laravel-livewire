<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use NotificationChannels\WebPush\WebPushMessage;

class AttendanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Schedule $schedule;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule, $data = [])
    {
        $this->schedule = $schedule;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return notificationVia($notifiable, true, true, true);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $signedUrl = URL::temporarySignedRoute('signed.schedule.ask', now()->addDay(), [
            'schedule' => $this->schedule->uuid,
            'dealer' => $notifiable->id,
        ]);

        return (new MailMessage)
            ->subject(trans('schedules.notification.subject'))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]))
            ->line(trans('schedules.notification.title', ['name' => $this->schedule->user->given_names]))
            ->action(trans('schedules.notification.deal_text'), $signedUrl);
    }

    public function toWebPush($notifiable, $notification)
    {
        $body = trans('schedules.notification.title', ['name' => $this->schedule->user->given_names]);

        $signedUrl = URL::temporarySignedRoute('signed.schedule.ask', now()->addDay(), [
            'schedule' => $this->schedule->uuid,
            'dealer' => $notifiable->id,
        ]);

        return (new WebPushMessage)
            ->title(trans('schedules.notification.subject'))
            ->icon(get_push_notification_icon())
            ->body($body)
            ->action(trans('schedules.notification.deal_text'), $signedUrl)
            ->data([
                'type' => 'schedule-approval',
                'id' => $notification->id,
                'body' => $body,
            ]);
    }

    /**
     * Get the array representation of the
     *  notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'target_avatar' => $this->schedule->user->avatar_url,
            'target_user' => $this->schedule->user->given_names,
        ];
    }

    public function toLxSms($notifiable)
    {
        $body = trans('schedules.notification.title', ['name' => $this->schedule->user->given_names]);
        $body .= PHP_EOL;
        $body .= $this->schedule->approval_description;

        return [
            'text' => $body,
        ];
    }
}
