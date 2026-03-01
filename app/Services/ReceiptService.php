<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    public function generateReceipt(Order $order): string
    {
        if ($order->hasReceipt()) {
            return $order->receipt_path;
        }

        $receiptNumber = $this->generateReceiptNumber($order);
        
        $order->update([
            'receipt_number' => $receiptNumber,
            'receipt_generated_at' => now(),
        ]);

        $pdf = $this->createPDF($order);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = "receipts/{$order->user_id}/{$filename}";
        
        Storage::disk('public')->put($path, $pdf->output());
        
        $order->update(['receipt_path' => $path]);
        
        return $path;
    }

    protected function generateReceiptNumber(Order $order): string
    {
        $prefix = config('app.receipt_prefix', 'TH-RCP');
        $timestamp = now()->format('Ymd');
        $orderId = str_pad($order->id, 6, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$timestamp}-{$orderId}";
    }

    protected function createPDF(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['user', 'seller', 'items.product']);
        
        $data = [
            'order' => $order,
            'receiptNumber' => $order->receipt_number,
            'generatedAt' => $order->receipt_generated_at ?? now(),
            'companyName' => config('app.name', 'ToyHaven'),
            'companyAddress' => config('app.company_address', 'Philippines'),
            'companyPhone' => config('app.company_phone', ''),
            'companyEmail' => config('app.company_email', config('mail.from.address')),
        ];

        return Pdf::loadView('pdf.receipt', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    public function downloadReceipt(Order $order): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (!$order->hasReceipt()) {
            $this->generateReceipt($order);
        }

        $filePath = Storage::disk('public')->path($order->receipt_path);
        
        return response()->download($filePath, "Receipt_{$order->receipt_number}.pdf");
    }

    public function getReceiptUrl(Order $order): ?string
    {
        if (!$order->hasReceipt()) {
            return null;
        }

        return Storage::disk('public')->url($order->receipt_path);
    }

    public function regenerateReceipt(Order $order): string
    {
        if ($order->hasReceipt()) {
            Storage::disk('public')->delete($order->receipt_path);
        }

        $order->update([
            'receipt_path' => null,
            'receipt_number' => null,
            'receipt_generated_at' => null,
        ]);

        return $this->generateReceipt($order);
    }
}
