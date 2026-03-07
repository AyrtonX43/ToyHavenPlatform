<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Models\AuctionTrackingUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function store(Request $request, AuctionPayment $auctionPayment)
    {
        $seller = $auctionPayment->auction->getSellerUser();

        if ($seller->id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'tracking_number' => 'required|string|max:100',
            'carrier' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        AuctionTrackingUpdate::create([
            'auction_payment_id' => $auctionPayment->id,
            'tracking_number' => $request->tracking_number,
            'carrier' => $request->carrier,
            'notes' => $request->notes,
            'updated_by' => Auth::id(),
        ]);

        $auctionPayment->update([
            'tracking_number' => $request->tracking_number,
            'delivery_status' => 'shipped',
            'shipped_at' => $auctionPayment->shipped_at ?? now(),
        ]);

        return back()->with('success', 'Tracking added.');
    }
}
