<?php

namespace App\Notifications;

use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class NoteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $note;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Note $note, $data = [])
    {
        $this->note = $note;
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
        [$subject, $line, $url] = $this->getModelDetailAccordingToType();

        return (new MailMessage)
            ->subject($subject)
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]))
            ->line($line)
            ->action(trans('notes.notification.action_link_text'), $url);
    }

    private function getModelDetailAccordingToType()
    {
        $targetUser = $this->note->user;

        $url = route('users.edit', [
            'user' => $targetUser->uuid,
            'type' => 'notes',
        ]);

        $subject = trans('notes.notification.new_note_subject');
        $line = trans('notes.notification.new_note_added', ['name' => $targetUser->given_names, 'title' => $this->note->title]);
        $notificationType = 'create';

        if ($this->data && count($this->data) && $this->data['type'] == 'update') {
            $notificationType = 'update';
            $subject = trans('notes.notification.updated_note_subject');
            $line = trans('notes.notification.note_is_updated', ['name' => $targetUser->given_names, 'title' => $this->note->title]);
        }

        return [$subject, $line, $url, $notificationType, $targetUser];
    }

    public function toDatabase()
    {
        [$subject, $line, $url, $notificationType, $targetUser] = $this->getModelDetailAccordingToType();

        return [
            'target_user' => $targetUser->given_names,
            'target_avatar' => $targetUser->avatar_url,
            'action_url' => $url,
            'type' => $notificationType,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        [$subject, $line, $url, $notificationType] = $this->getModelDetailAccordingToType();

        $typeMaps = [
            'create' => 'note-created',
            'update' => 'note-updated',
            'delete' => 'note-deleted',
        ];

        return (new WebPushMessage)
            ->title($subject)
            ->icon(get_push_notification_icon())
            ->body($line)
            ->action(trans('notes.notification.action_link_text'), $url)
            ->data([
                'type' => $typeMaps[$notificationType],
                'id' => $notification->id,
            ]);
    }
}
