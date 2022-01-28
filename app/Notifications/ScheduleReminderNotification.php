<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class ScheduleReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $children;

    public function __construct($children = [])
    {
        $this->children = $children;
    }

    public function via($notifiable)
    {
        return notificationVia($notifiable, true, false, true, true);
    }

    public function toMail($notifiable)
    {
        [$subject, $line, $actionText, $url] = $this->getNotificationData($notifiable);

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]))
            ->line($line);

        foreach ($this->children as $child) {
            $mail->line('•  '.$child.PHP_EOL);
        }

        return $mail->action($actionText, $url);
    }

    public function toWebPush($notifiable, $notification)
    {
        [$subject, $line, $actionText, $url] = $this->getNotificationData($notifiable);

        $line .= PHP_EOL;
        foreach ($this->children as $child) {
            $line .= '•  '.$child.PHP_EOL;
        }

        return (new WebPushMessage)
            ->title($subject)
            ->icon(get_push_notification_icon())
            ->body($line)
            ->action($actionText, $url)
            ->data([
                'type' => 'schedule-reminder',
                'id' => $notification->id,
            ]);
    }

    private function getNotificationData($notifiable)
    {
        $subject = trans('schedules.notification.reminders.subject');
        $line = trans('schedules.notification.reminders.mail_line');
        $actionText = trans('schedules.notification.reminders.go_to_link');
        $url = route('schedules.index');

        return [$subject, $line, $actionText, $url];
    }

    public function toLxSms($notifiable)
    {
        [$subject, $line, $actionText, $url] = $this->getNotificationData($notifiable);

        $body = $subject;
        $body .= PHP_EOL;
        $body .= $line;
        $body .= PHP_EOL;

        foreach ($this->children as $child) {
            $body .= '•  '.$child.PHP_EOL;
        }

        $body .= $url;

        return [
            'text' => $body,
        ];
    }
}
