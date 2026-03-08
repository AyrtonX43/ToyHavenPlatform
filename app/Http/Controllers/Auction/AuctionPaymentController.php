<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionPaymentController extends Controller
{
    public function show(AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        if ($payment->isPaid()) {
            return redirect()->route('auction.payment.success', $payment)
                ->with('info', 'This payment has already been completed.');
        }

        $payment->load(['auction', 'winner']);

        return view('auction.payment.show', compact('payment'));
    }

    public function success(AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        $payment->load(['auction']);

        return view('auction.payment.success', compact('payment'));
    }

    public function markShipped(AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->auction->user_id !== $user->id) {
            abort(403);
        }
        if (! $payment->isPaid()) {
            return back()->with('error', 'Payment must be completed first.');
        }
        $payment->update([
            'delivery_status' => 'shipped',
        ]);
        return back()->with('success', 'Marked as shipped.');
    }

    public function confirmDelivery(AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->winner_id !== $user->id) {
            abort(403);
        }
        $payment->update([
            'delivery_status' => 'delivered',
            'confirmed_at' => now(),
        ]);
        return back()->with('success', 'Delivery confirmed. Thank you!');
    }
}
