<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class MessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Conversation $conversation;
    public $messages;
    public $messageIds;
    public $url;
    private User $sender;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Conversation $conversation, $data = [])
    {
        $this->conversation = $conversation;
        $this->messages = $data['messages'];
        $this->messageIds = $data['messages_ids'];

        $this->sender = User::find($data['sender_id']);

        $this->url = route('messages.index', [
            'conversation' => encrypt($conversation->id),
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return notificationVia($notifiable, true, true, false, true);
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject(trans('messages.notifications.message_received'))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->given_names]));

        $mail->line(trans('messages.notifications.received_msg_from', [
            'name' => $this->sender->given_names,
        ]));
        foreach ($this->messages as $message) {
            $mail->line($message);
        }

        $mail->action(trans('messages.notifications.link'), $this->url);

        return $mail;
    }

    public function toDatabase()
    {
        return [
            'sender' => $this->sender->given_names,
            'sender_avatar' => $this->sender->avatar_url,
            'message' => implode("\n", $this->messages),
            'messages_ids' => $this->messageIds,
        ];
    }

    public function toWebPush($notifiable, $notification)
    {
        $body = implode("\n", $this->messages);

        return (new WebPushMessage)
            ->title(trans('messages.notifications.message_received'))
            ->icon(get_push_notification_icon())
            ->body($body)
            ->action(trans('messages.notifications.view'), $this->url)
            ->data([
                'type' => 'conversation-message',
                'id' => $notification->id,
                'body' => [
                    'model_id' => $this->conversation->id,
                    'text1' => trans('messages.notifications.received_msg_from', [
                        'name' => $this->sender->given_names,
                    ]),
                    'text2' => $body,
                ],
            ]);
    }

    public function toLxSms($notifiable)
    {
        $message = implode("\n", $this->messages);

        $body = $this->conversation->organization->name;
        $body .= PHP_EOL;
        $body .= trans('messages.notifications.received_msg_from', [
            'name' => $this->sender->given_names,
        ]);
        $body .= PHP_EOL;
        $body .= $message;

        $body .= PHP_EOL;
        $body .= ShortUrl::linkFor($this->url);

        return [
            'text' => $body,
        ];
    }
}
