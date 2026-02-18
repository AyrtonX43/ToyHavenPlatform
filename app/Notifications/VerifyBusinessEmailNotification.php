<?php

namespace App\Notifications;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyBusinessEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Seller $seller,
        public string $email
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl();

        return (new MailMessage)
            ->to($this->email)
            ->subject('Verify Your Business Email – ToyHaven Platform')
            ->greeting('Hello ' . ($notifiable->name ?? 'Seller') . ',')
            ->line('You have requested to change your business contact email to **' . $this->email . '**.')
            ->line('Please click the button below to verify this email address for your business page.')
            ->action('Verify Business Email', $verificationUrl)
            ->line('This link will expire in 60 minutes. If you did not request this change, you can ignore this email.')
            ->salutation('Regards,' . "\n" . 'The ToyHaven Platform Team');
    }

    protected function verificationUrl(): string
    {
        return URL::temporarySignedRoute(
            'seller.business-page.verify-email',
            Carbon::now()->addMinutes(60),
            [
                'seller' => $this->seller->id,
                'email' => $this->email,
            ]
        );
    }
}
