<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Models\AuctionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionReviewController extends Controller
{
    public function store(Request $request, AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        if ($payment->delivery_status !== 'delivered' && $payment->delivery_status !== 'confirmed') {
            return back()->with('error', 'You must confirm delivery before leaving a review.');
        }

        if (AuctionReview::where('auction_payment_id', $payment->id)->exists()) {
            return back()->with('error', 'You have already reviewed this auction.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
        ]);

        AuctionReview::create([
            'auction_payment_id' => $payment->id,
            'winner_id' => $user->id,
            'auction_id' => $payment->auction_id,
            'seller_user_id' => $payment->auction->user_id,
            'rating' => $request->rating,
            'feedback' => $request->feedback,
            'delivery_confirmed_at' => $payment->confirmed_at ?? now(),
        ]);

        return back()->with('success', 'Thank you for your review!');
    }
}
