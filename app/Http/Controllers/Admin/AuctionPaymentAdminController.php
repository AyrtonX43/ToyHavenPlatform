<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;

class AuctionPaymentAdminController extends Controller
{
    public function index()
    {
        $payments = AuctionPayment::with(['auction', 'winner'])
            ->whereIn('status', [AuctionPayment::STATUS_HELD, AuctionPayment::STATUS_PENDING])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.auction-payments.index', compact('payments'));
    }

    public function release(AuctionPayment $auctionPayment)
    {
        if (! $auctionPayment->canRelease()) {
            return back()->with('error', 'Can only release when delivery is confirmed.');
        }

        $auctionPayment->update(['status' => AuctionPayment::STATUS_RELEASED]);

        return back()->with('success', 'Payment released to seller.');
    }

    public function refund(AuctionPayment $auctionPayment)
    {
        $auctionPayment->update(['status' => AuctionPayment::STATUS_REFUNDED]);

        return back()->with('success', 'Payment refunded.');
    }
}
