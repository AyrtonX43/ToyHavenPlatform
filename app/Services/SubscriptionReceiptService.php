<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubscriptionReceiptService
{
    public function generateReceipt(SubscriptionPayment $payment): string
    {
        if ($payment->hasReceipt()) {
            return $payment->receipt_path;
        }

        $receiptNumber = $this->generateReceiptNumber($payment);

        $pdf = $this->createPDF($payment, $receiptNumber);

        $filename = "membership_receipt_{$receiptNumber}.pdf";
        $path = "receipts/subscriptions/{$payment->subscription_id}/{$filename}";

        $directory = "receipts/subscriptions/{$payment->subscription_id}";
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        Storage::disk('public')->put($path, $pdf->output());

        $payment->update([
            'receipt_number' => $receiptNumber,
            'receipt_path' => $path,
            'receipt_generated_at' => now(),
        ]);

        return $path;
    }

    protected function generateReceiptNumber(SubscriptionPayment $payment): string
    {
        $prefix = config('app.receipt_prefix', 'TH');
        $timestamp = now()->format('Ymd');
        $id = str_pad($payment->id, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-SUB-{$timestamp}-{$id}";
    }

    protected function createPDF(SubscriptionPayment $payment, string $receiptNumber): \Barryvdh\DomPDF\PDF
    {
        $payment->load(['subscription.plan', 'subscription.user']);

        $logoPath = public_path('images/logo.png');
        $data = [
            'subscriptionPayment' => $payment,
            'subscription' => $payment->subscription,
            'plan' => $payment->subscription->plan,
            'receiptNumber' => $receiptNumber,
            'generatedAt' => $payment->receipt_generated_at ?? now(),
            'companyName' => config('app.name', 'ToyHaven'),
            'companyAddress' => config('app.company_address', 'Philippines'),
            'companyEmail' => config('mail.from.address'),
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

    public function downloadReceipt(SubscriptionPayment $payment): BinaryFileResponse
    {
        if (! $payment->hasReceipt()) {
            $this->generateReceipt($payment);
        }

        $filePath = Storage::disk('public')->path($payment->receipt_path);

        $receiptNumber = $this->generateReceiptNumber($payment);

        return response()->download($filePath, "Membership_Receipt_{$receiptNumber}.pdf");
    }
}
