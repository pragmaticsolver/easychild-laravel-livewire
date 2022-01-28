<?php

namespace App\Notifications;

use App\Models\ParentLink;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use NotificationChannels\WebPush\WebPushMessage;

class NewChildLinkedToParentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $route;
    public $child;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ParentLink $parentLink)
    {
        $this->child = User::findOrFail($parentLink->child_id);

        $this->route = URL::signedRoute('parent.signup', ['token' => $parentLink->token]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return notificationVia($notifiable, true, false, true, true, true);
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
            ->subject(trans('users.parent.new-email.new-child-linked-subject'))
            ->view('emails.new-parent-created', [
                'qrCode' => getQrCodeForLoginLink($notifiable, $this->route),
                'user' => $notifiable,
                'loginRoute' => $this->route,
                'organization' => $this->child->organization,
                'child' => $this->child,
                'onlyChildLinked' => true,
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

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title(trans('users.parent.new-email.new-child-linked-subject'))
            ->icon(get_push_notification_icon())
            ->body(trans('users.parent.new-email.child_linked_para', ['name' => $this->child->given_names]))
            ->action(trans('users.parent.new-email.login_link_text_new_linked'), $this->route)
            ->data([
                'type' => 'new-child-linked',
                'id' => $notification->id,
            ]);
    }

    public function toLxSms($notifiable)
    {
        $body = $this->child->organization->name;
        $body .= PHP_EOL;
        $body .= trans('users.parent.new-email.child_linked_para', [
            'name' => $this->child->given_names
        ]);

        $body .= PHP_EOL;
        $body .= ShortUrl::linkFor($this->route);

        return [
            'text' => $body,
        ];
    }
}
