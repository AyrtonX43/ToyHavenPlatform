<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class SubscriptionReceiptService
{
    public function generateReceipt(SubscriptionPayment $subscriptionPayment): string
    {
        if ($subscriptionPayment->hasReceipt()) {
            return $subscriptionPayment->receipt_path;
        }

        $subscriptionPayment->load(['subscription.plan', 'subscription.user']);
        $subscription = $subscriptionPayment->subscription;

        $receiptNumber = $this->generateReceiptNumber($subscriptionPayment);

        $subscriptionPayment->update([
            'receipt_number' => $receiptNumber,
            'receipt_generated_at' => now(),
        ]);

        $pdf = $this->createPDF($subscriptionPayment);

        $filename = "subscription_receipt_{$receiptNumber}.pdf";
        $path = "receipts/subscriptions/{$subscription->user_id}/{$filename}";

        $directory = "receipts/subscriptions/{$subscription->user_id}";
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        Storage::disk('public')->put($path, $pdf->output());

        $subscriptionPayment->update(['receipt_path' => $path]);

        return $path;
    }

    protected function generateReceiptNumber(SubscriptionPayment $subscriptionPayment): string
    {
        $prefix = config('app.receipt_prefix', 'TH-SUB');
        $timestamp = now()->format('Ymd');
        $id = str_pad($subscriptionPayment->id, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$timestamp}-{$id}";
    }

    protected function createPDF(SubscriptionPayment $subscriptionPayment): \Barryvdh\DomPDF\PDF
    {
        $subscriptionPayment->load(['subscription.plan', 'subscription.user']);
        $subscription = $subscriptionPayment->subscription;
        $plan = $subscription->plan;
        $user = $subscription->user;

        $logoPath = public_path('images/logo.png');
        $data = [
            'subscriptionPayment' => $subscriptionPayment,
            'subscription' => $subscription,
            'plan' => $plan,
            'user' => $user,
            'receiptNumber' => $subscriptionPayment->receipt_number,
            'generatedAt' => $subscriptionPayment->receipt_generated_at ?? now(),
            'companyName' => config('app.name', 'ToyHaven'),
            'companyAddress' => config('app.company_address', 'Philippines'),
            'companyPhone' => config('app.company_phone', ''),
            'companyEmail' => config('app.company_email', config('mail.from.address')),
            'logoPath' => file_exists($logoPath) ? $logoPath : null,
        ];

        return Pdf::loadView('pdf.subscription-receipt', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    public function downloadReceipt(SubscriptionPayment $subscriptionPayment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (! $subscriptionPayment->hasReceipt()) {
            $this->generateReceipt($subscriptionPayment);
        }

        $filePath = Storage::disk('public')->path($subscriptionPayment->receipt_path);

        return response()->download($filePath, "Subscription_Receipt_{$subscriptionPayment->receipt_number}.pdf");
    }
}
