<?php

namespace App\Notifications;

use App\Models\SubscriptionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class MembershipPaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected SubscriptionPayment $subscriptionPayment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->subscriptionPayment->load(['subscription.plan', 'subscription.user']);
        $subscription = $this->subscriptionPayment->subscription;
        $plan = $subscription->plan;

        $receiptService = app(\App\Services\SubscriptionReceiptService::class);
        $receiptService->generateReceipt($this->subscriptionPayment);

        $message = (new MailMessage)
            ->subject('Membership Payment Successful - ' . $plan->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your membership payment has been successfully processed.')
            ->line('**Plan:** ' . $plan->name)
            ->line('**Amount:** ₱' . number_format($this->subscriptionPayment->amount, 2))
            ->line('**Payment Method:** ' . strtoupper($this->subscriptionPayment->payment_method ?? 'QRPH'))
            ->line('')
            ->action('View Membership', route('membership.manage'))
            ->line('Your receipt is attached to this email.')
            ->line('Thank you for being a ToyHaven member!');

        if ($this->subscriptionPayment->hasReceipt()) {
            $receiptPath = Storage::disk('public')->path($this->subscriptionPayment->receipt_path);
            if (file_exists($receiptPath)) {
                $receiptNumber = $this->subscriptionPayment->receipt_number ?? 'receipt';
                $message->attach($receiptPath, [
                    'as' => "Membership_Receipt_{$receiptNumber}.pdf",
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $this->subscriptionPayment->load('subscription.plan');

        return [
            'type' => 'membership_payment_success',
            'subscription_payment_id' => $this->subscriptionPayment->id,
            'plan_name' => $this->subscriptionPayment->subscription->plan->name,
            'amount' => $this->subscriptionPayment->amount,
            'message' => 'Your ' . $this->subscriptionPayment->subscription->plan->name . ' membership payment of ₱' . number_format($this->subscriptionPayment->amount, 2) . ' was successful. Receipt sent via email.',
            'action_url' => route('membership.manage'),
            'action_text' => 'View Membership',
        ];
    }
}
