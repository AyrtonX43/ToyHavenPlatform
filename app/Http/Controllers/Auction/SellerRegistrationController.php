<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SellerRegistrationController extends Controller
{
    /**
     * Auction seller registration (stub - full flow in later phase).
     * Requires VIP membership via EnsureVipForAuctionSeller middleware.
     */
    public function show(Request $request)
    {
        $type = $request->route('type', 'individual');

        return view('auction.seller-registration.stub', [
            'type' => in_array($type, ['individual', 'business']) ? $type : 'individual',
        ]);
    }
}
