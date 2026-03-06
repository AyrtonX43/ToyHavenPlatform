<?php

namespace App\Services;

use App\Models\AuctionPayment;
use App\Notifications\AuctionPaymentReceiptNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AuctionReceiptService
{
    /**
     * Generate receipt, send email with PDF attachment, and create notification.
     */
    public function generateAndSendReceipt(AuctionPayment $auctionPayment): string
    {
        $path = $this->generateReceipt($auctionPayment);
        $auctionPayment->refresh();

        $winner = $auctionPayment->winner;
        if ($winner && $winner->email) {
            try {
                $pdf = $this->createPDF($auctionPayment);
                $filename = "auction_receipt_{$auctionPayment->receipt_number}.pdf";

                Mail::send([], [], function ($message) use ($winner, $auctionPayment, $pdf, $filename) {
                    $message->to($winner->email, $winner->name)
                        ->subject('Your Official Receipt - ' . $auctionPayment->auction->title . ' | ToyHaven')
                        ->html($this->getReceiptEmailHtml($auctionPayment))
                        ->attachData($pdf->output(), $filename, ['mime' => 'application/pdf']);
                });

                $winner->notify(new AuctionPaymentReceiptNotification($auctionPayment));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $path;
    }

    protected function getReceiptEmailHtml(AuctionPayment $auctionPayment): string
    {
        $auction = $auctionPayment->auction;

        return '<div style="font-family: sans-serif; max-width: 600px;">
            <h2 style="color: #0891b2;">Payment Received – Official Receipt</h2>
            <p>Dear ' . e($auctionPayment->winner->name ?? 'Customer') . ',</p>
            <p>Thank you for your payment. Your official receipt for <strong>' . e($auction->title) . '</strong> is attached to this email.</p>
            <p><strong>Receipt No:</strong> ' . e($auctionPayment->receipt_number ?? 'N/A') . '<br>
            <strong>Amount Paid:</strong> ₱' . number_format($auctionPayment->total_amount, 2) . '</p>
            <p>You can track your order from your <a href="' . route('auctions.wins.show', $auctionPayment) . '">Auction Wins</a> page.</p>
            <p>Thank you for choosing ToyHaven!</p>
        </div>';
    }

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
