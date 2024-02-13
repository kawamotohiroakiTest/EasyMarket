<?php

namespace App\Notifications\easymarket;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SignupVerify extends Notification
{
    use Queueable;

    /**
     * @var string
     */
    public $verificationUrl;

    /**
     * @param  string $verificationUrl
     * @return void
     */
    public function __construct(string $verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('【easymarket】会員本登録のご案内')
            ->view('emails.easymarket.signup_verify', [
                'verificationUrl' => $this->verificationUrl,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
