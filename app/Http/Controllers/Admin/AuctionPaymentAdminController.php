<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;

class AuctionPaymentAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionPayment::with(['auction', 'winner', 'seller']);

        if ($request->filled('status')) {
            $query->where('escrow_status', $request->status);
        }

        $payments = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.payments.index', compact('payments'));
    }

    public function show(AuctionPayment $auctionPayment)
    {
        $auctionPayment->load(['auction.images', 'auction.category', 'winner', 'seller']);

        return view('admin.auctions.payments.show', compact('auctionPayment'));
    }

    public function releaseEscrow(AuctionPayment $auctionPayment)
    {
        if ($auctionPayment->escrow_status !== 'held') {
            return back()->with('error', 'Escrow is not in held status.');
        }

        $auctionPayment->update([
            'escrow_status' => 'released',
            'released_at' => now(),
        ]);

        return back()->with('success', 'Escrow released to seller.');
    }

    public function refund(AuctionPayment $auctionPayment)
    {
        if (! in_array($auctionPayment->escrow_status, ['held', 'disputed'])) {
            return back()->with('error', 'Cannot refund from current status.');
        }

        $auctionPayment->update([
            'payment_status' => 'refunded',
            'escrow_status' => 'refunded',
        ]);

        return back()->with('success', 'Payment marked as refunded.');
    }
}
