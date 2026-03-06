<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Models\AuctionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, AuctionPayment $auctionPayment)
    {
        if ($auctionPayment->winner_id !== Auth::id()) {
            abort(403);
        }

        if (! $auctionPayment->winner_received_confirmed_at) {
            return back()->with('error', 'Please confirm receipt before leaving a review.');
        }

        $request->validate([
            'rating_listing' => 'required|integer|min:1|max:5',
            'rating_seller' => 'required|integer|min:1|max:5',
            'feedback_listing' => 'nullable|string|max:1000',
            'feedback_seller' => 'nullable|string|max:1000',
        ]);

        $auctionPayment->load('auction');

        AuctionReview::updateOrCreate(
            [
                'auction_payment_id' => $auctionPayment->id,
                'for_listing' => true,
            ],
            [
                'user_id' => Auth::id(),
                'auction_id' => $auctionPayment->auction_id,
                'auction_seller_profile_id' => $auctionPayment->auction->auction_seller_profile_id,
                'rating' => $request->rating_listing,
                'feedback' => $request->feedback_listing,
            ]
        );

        AuctionReview::updateOrCreate(
            [
                'auction_payment_id' => $auctionPayment->id,
                'for_listing' => false,
            ],
            [
                'user_id' => Auth::id(),
                'auction_id' => $auctionPayment->auction_id,
                'auction_seller_profile_id' => $auctionPayment->auction->auction_seller_profile_id,
                'rating' => $request->rating_seller,
                'feedback' => $request->feedback_seller,
            ]
        );

        return back()->with('success', 'Thank you for your review!');
    }
}
