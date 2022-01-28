<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class NewPrincipalCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $password;
    public User $user;
    public $token;
    public $isPasswordReset;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $password, $isPasswordReset = false)
    {
        $this->isPasswordReset = $isPasswordReset;
        $this->password = $password;
        $this->user = $user;

        $this->token = $user->token;
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
        $loginRoute = URL::signedRoute('login', ['token' => $this->token]);

        return (new MailMessage)
            ->subject($this->isPasswordReset ? trans('mail.reset_password.subject') : trans('mail.user_created'))
            ->view('emails.new-principal-created', [
                'manager' => $notifiable,
                'user' => $this->user,
                'password' => $this->password,
                'qrCode' => getQrCodeForLoginLink($this->user),
                'token' => $this->token,
                'loginRoute' => $loginRoute,
                'isPasswordReset' => $this->isPasswordReset,
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
}
