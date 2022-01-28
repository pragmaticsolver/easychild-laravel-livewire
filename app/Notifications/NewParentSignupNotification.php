<?php

namespace App\Notifications;

use App\Models\ParentLink;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewParentSignupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $password;
    public $route;
    public $child;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $parent, ParentLink $parentLink)
    {
        $this->password = Str::random(config('setting.auth.passwordLength'));

        $parent->update([
            'password' => $this->password,
            'token' => Str::random(config('setting.auth.tokenLength')),
        ]);

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
        return notificationVia($notifiable, true, false, false, false, true);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject(trans('users.parent.new-email.subject'))
            ->view('emails.new-parent-created', [
                'qrCode' => getQrCodeForLoginLink($notifiable, $this->route),
                'user' => $notifiable,
                'loginRoute' => $this->route,
                'organization' => $this->child->organization,
                'child' => $this->child,
                'onlyChildLinked' => false,
            ]);

        $file = getManualPdf($notifiable);

        if ($file) {
            $mail->attach($file, [
                'as' => 'handbuch.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
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
}
