<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset Your Password – ToyHaven Platform')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('We received a request to reset the password for the account associated with this email address.')
            ->line('To set a new password, please click the button below. You will be taken to a secure page where you can enter your new password.')
            ->action('Reset Password', $url)
            ->line('This link is valid for 60 minutes. After that, you will need to submit a new password reset request.')
            ->line('If you did not request a password reset, please ignore this email. Your password will remain unchanged. If you are concerned about your account security, we recommend changing your password after signing in.')
            ->line('If the button above does not work, copy and paste the following link into your browser:')
            ->line($url)
            ->salutation('Regards,' . "\n" . 'The ToyHaven Platform Team');
    }
}
