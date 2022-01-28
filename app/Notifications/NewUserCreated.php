<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewUserCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $password;
    public $token;
    public $qrCode;
    public $isPasswordReset = false;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $data = [])
    {
        $this->password = Str::random(config('setting.auth.passwordLength'));
        $this->token = Str::random(config('setting.auth.tokenLength'));
        $user->update([
            'password' => $this->password,
            'token' => $this->token,
        ]);

        if ($data && count($data) && Arr::has($data, 'password-reset') && $data['password-reset']) {
            $this->isPasswordReset = true;
        }

        $this->user = $user;
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
        $loginRoute = route('login');

        if (in_array($this->user->role, ['User', 'Parent'])) {
            $loginRoute = URL::signedRoute('login', ['token' => $this->token]);
        }

        $mail = (new MailMessage)
            ->subject($this->isPasswordReset ? trans('mail.reset_password.subject') : trans('mail.user_created'))
            ->view('emails.new-user-created', [
                'qrCode' => getQrCodeForLoginLink($this->user),
                'user' => $this->user,
                'password' => $this->password,
                'token' => $this->token,
                'loginRoute' => $loginRoute,
                'isPasswordReset' => $this->isPasswordReset,
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
