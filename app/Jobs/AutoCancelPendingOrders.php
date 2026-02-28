<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCancelPendingOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $cutoffTime = now()->subHours(24);

        $pendingOrders = Order::where('payment_status', 'pending')
            ->where('status', 'pending')
            ->where('created_at', '<=', $cutoffTime)
            ->whereNull('cancelled_at')
            ->get();

        foreach ($pendingOrders as $order) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => 'Payment not completed within 24 hours (auto-cancelled)',
            ]);

            $order->restoreStock();

            Log::info('Auto-cancelled order', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            if ($order->seller && $order->seller->user) {
                $order->seller->user->notify(new \App\Notifications\OrderCancelledNotification($order));
            }
        }

        Log::info('Auto-cancel job completed', [
            'cancelled_count' => $pendingOrders->count(),
        ]);
    }
}
