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
        protected SubscriptionPayment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->payment->load(['subscription.plan', 'subscription.user']);
        $plan = $this->payment->subscription->plan;
        $amount = $this->payment->amount;

        $message = (new MailMessage)
            ->subject('Membership Payment Successful - ToyHaven')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your membership payment has been successfully processed.')
            ->line('**Plan:** ' . $plan->name)
            ->line('**Amount:** ₱' . number_format($amount, 2))
            ->line('Your receipt is attached to this email.')
            ->line('Thank you for being a ToyHaven member!');

        if ($this->payment->hasReceipt()) {
            $receiptPath = Storage::disk('public')->path($this->payment->receipt_path);
            if (file_exists($receiptPath)) {
                $receiptNumber = $this->generateReceiptNumber();
                $message->attach($receiptPath, [
                    'as' => 'Membership_Receipt_' . $receiptNumber . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $this->payment->load(['subscription.plan']);

        return [
            'type' => 'membership_payment_success',
            'subscription_payment_id' => $this->payment->id,
            'plan_name' => $this->payment->subscription->plan->name,
            'amount' => (float) $this->payment->amount,
            'message' => 'Your ' . $this->payment->subscription->plan->name . ' membership payment of ₱' . number_format($this->payment->amount, 2) . ' was successful. Receipt sent to your email.',
            'action_url' => route('membership.manage'),
            'action_text' => 'Manage Membership',
        ];
    }

    protected function generateReceiptNumber(): string
    {
        $prefix = config('app.receipt_prefix', 'TH');
        $timestamp = now()->format('Ymd');
        $id = str_pad($this->payment->id, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-SUB-{$timestamp}-{$id}";
    }
}
