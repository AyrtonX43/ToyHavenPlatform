<?php

namespace App\Services;

use App\Models\AuctionPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class AuctionReceiptService
{
    public function generateReceipt(AuctionPayment $auctionPayment): string
    {
        if ($auctionPayment->receipt_path) {
            return $auctionPayment->receipt_path;
        }

        $auctionPayment->load(['auction', 'winner', 'seller']);

        $receiptNumber = $this->generateReceiptNumber($auctionPayment);

        $auctionPayment->update([
            'receipt_number' => $receiptNumber,
            'receipt_generated_at' => now(),
        ]);

        $pdf = $this->createPDF($auctionPayment);

        $filename = "auction_receipt_{$receiptNumber}.pdf";
        $path = "receipts/auctions/{$auctionPayment->winner_id}/{$filename}";

        $directory = "receipts/auctions/{$auctionPayment->winner_id}";
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        Storage::disk('public')->put($path, $pdf->output());

        $auctionPayment->update(['receipt_path' => $path]);

        return $path;
    }

    protected function generateReceiptNumber(AuctionPayment $auctionPayment): string
    {
        $prefix = config('app.receipt_prefix', 'TH-AUC');
        $timestamp = now()->format('Ymd');
        $id = str_pad($auctionPayment->id, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$timestamp}-{$id}";
    }

    protected function createPDF(AuctionPayment $auctionPayment): \Barryvdh\DomPDF\PDF
    {
        $auctionPayment->load(['auction.images', 'auction.category', 'winner', 'seller']);

        $logoPath = public_path('images/logo.png');
        $data = [
            'payment' => $auctionPayment,
            'auction' => $auctionPayment->auction,
            'receiptNumber' => $auctionPayment->receipt_number,
            'generatedAt' => $auctionPayment->receipt_generated_at ?? now(),
            'companyName' => config('app.name', 'ToyHaven'),
            'companyAddress' => config('app.company_address', 'Philippines'),
            'companyEmail' => config('app.company_email', config('mail.from.address')),
            'logoPath' => file_exists($logoPath) ? $logoPath : null,
        ];

        return Pdf::loadView('pdf.auction-receipt', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    public function downloadReceipt(AuctionPayment $auctionPayment): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if (! $auctionPayment->receipt_path) {
            $this->generateReceipt($auctionPayment);
        }

        $filePath = Storage::disk('public')->path($auctionPayment->receipt_path);

        return response()->download($filePath, "Auction_Receipt_{$auctionPayment->receipt_number}.pdf");
    }
}
