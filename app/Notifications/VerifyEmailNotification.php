<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->subject('Verify Your Email Address – ToyHaven Platform')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Thank you for creating an account with ToyHaven Platform.')
            ->line('To complete your registration and activate your account, please verify your email address by clicking the button below.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This link is valid for 24 hours. If you do not verify within this time, you will need to request a new verification email from your account settings.')
            ->line('If you did not create an account with us, you may safely ignore this email. No action is required.')
            ->line('If the button above does not work, copy and paste the following link into your browser:')
            ->line($verificationUrl)
            ->salutation('Regards,' . "\n" . 'The ToyHaven Platform Team');
    }
}
