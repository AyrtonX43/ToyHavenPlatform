<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;

class AuctionPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionPayment::with(['auction.user', 'auction.images', 'winner'])->orderByDesc('created_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'paid', 'held', 'released', 'refunded'])) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20);

        $stats = [
            'total_held' => AuctionPayment::where('status', 'held')->sum('amount'),
            'total_released' => AuctionPayment::where('status', 'released')->sum('amount'),
            'awaiting_payment' => AuctionPayment::where('status', 'pending')->count(),
            'in_escrow' => AuctionPayment::where('status', 'held')->count(),
            'ready_to_release' => AuctionPayment::whereIn('status', ['paid', 'held'])
                ->whereIn('delivery_status', ['delivered', 'confirmed'])
                ->count(),
        ];

        return view('admin.auction-payments.index', compact('payments', 'stats'));
    }

    public function release(AuctionPayment $payment)
    {
        if (! in_array($payment->status, ['paid', 'held'])) {
            return back()->with('error', 'Only paid/held payments can be released.');
        }
        if ($payment->delivery_status !== 'delivered' && $payment->delivery_status !== 'confirmed') {
            return back()->with('error', 'Buyer must confirm delivery before releasing escrow.');
        }
        $payment->update([
            'status' => 'released',
            'released_at' => now(),
        ]);

        try {
            $payment->load('auction.user');
            $seller = $payment->auction->user;
            if ($seller) {
                $seller->notify(new \App\Notifications\EscrowReleasedNotification($payment->auction, $payment));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to notify seller of escrow release', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Escrow released to seller. Seller has been notified.');
    }
}
