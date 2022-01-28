<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class UserAttendanceDealedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Schedule $schedule;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Schedule $schedule, $data = [])
    {
        $this->schedule = $schedule;
        $this->data = $data;
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

    private function getScheduleMeta($notifiable)
    {
        $dealer = $this->schedule->dealer;
        $child = $this->schedule->user;

        $title = trans('schedules.notification.user_schedule_approved_title');
        $body = trans('schedules.notification.user_schedule_approved_msg_for_parent', [
            'child' => $child->given_names,
            'name' => $dealer->given_names,
        ]);

        if ($this->schedule->status != 'approved') {
            $title = trans('schedules.notification.user_schedule_rejected_title');
            $body = trans('schedules.notification.user_schedule_rejected_msg_for_parent', [
                'child' => $child->given_names,
                'name' => $dealer->given_names,
            ]);
        }

        $actionText = trans('schedules.notification.view');
        $actionUrl = route('schedules.index');

        return [$title, $body, $actionText, $actionUrl];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        [$title, $body, $actionText, $actionUrl] = $this->getScheduleMeta($notifiable);

        return (new MailMessage)
            ->subject($title)
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]))
            ->line($body)
            ->action($actionText, $actionUrl);
    }

    public function toWebPush($notifiable, $notification)
    {
        [$title, $body, $actionText, $actionUrl] = $this->getScheduleMeta($notifiable);

        return (new WebPushMessage)
            ->title($title)
            ->icon(get_push_notification_icon())
            ->body($body)
            ->action($actionText, $actionUrl)
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
        $child = $this->schedule->user;

        return [
            'target_avatar' => $this->schedule->dealer->avatar_url,
            'target_user' => $this->schedule->dealer->given_names,
            'target_child' => $child->given_names,
        ];
    }

    public function toLxSms($notifiable)
    {
        [$title, $body, $actionText, $actionUrl] = $this->getScheduleMeta($notifiable);

        $text = $title;
        $text .= PHP_EOL;
        $text .= $this->schedule->approval_description;
        $text .= PHP_EOL;
        $text .= $body;

        return [
            'text' => $text,
        ];
    }
}
