<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateOrderReceiptPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        $order = $this->order->load(['user', 'seller', 'items.product']);

        $pdf = Pdf::loadView('pdf.order-receipt', compact('order'));
        
        $fileName = 'receipt-' . $order->order_number . '.pdf';
        $pdfContent = $pdf->output();

        Mail::send('emails.order-receipt', compact('order'), function ($message) use ($order, $pdfContent, $fileName) {
            $message->to($order->user->email, $order->user->name)
                ->subject('Order Receipt - ' . $order->order_number)
                ->attachData($pdfContent, $fileName, [
                    'mime' => 'application/pdf',
                ]);
        });
    }
}
