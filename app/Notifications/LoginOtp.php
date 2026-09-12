<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginOtp extends Notification
{
    public function __construct(private readonly string $code) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your EasyTime Online verification code')
            ->greeting('Verify your sign in')
            ->line('Use the verification code below to finish signing in to the central administration panel.')
            ->line($this->code)
            ->line('This code expires in 10 minutes. If you did not try to sign in, you can ignore this email.');
    }
}
