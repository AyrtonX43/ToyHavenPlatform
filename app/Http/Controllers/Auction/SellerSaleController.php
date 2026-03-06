<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerSaleController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.seller-profile.index')
                ->with('error', 'You need an approved auction seller profile.');
        }

        $sales = AuctionPayment::with(['auction.images', 'auction.category', 'winner'])
            ->where('seller_user_id', $user->id)
            ->where('payment_status', 'paid')
            ->orderByDesc('paid_at')
            ->paginate(10);

        return view('auctions.seller.sales', compact('sales'));
    }

    public function show(AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->seller_user_id !== $user->id) {
            abort(403);
        }

        if ($auctionPayment->payment_status !== 'paid') {
            return redirect()->route('auctions.seller.sales.index')->with('error', 'Payment not found.');
        }

        $auctionPayment->load(['auction.images', 'auction.category', 'winner', 'trackingUpdates']);

        return view('auctions.seller.sale-show', compact('auctionPayment'));
    }
}
