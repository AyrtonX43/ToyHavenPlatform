<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionPaymentAdminController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->hasAuctionPermission('auction_payments_moderate')) {
            abort(403);
        }

        $query = AuctionPayment::with(['auction', 'winner', 'seller']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        if ($request->filled('escrow')) {
            $query->where('escrow_status', $request->escrow);
        }

        $payments = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auction-payments.index', compact('payments'))->with('context', 'moderator');
    }

    public function show(AuctionPayment $auctionPayment)
    {
        if (! Auth::user()->hasAuctionPermission('auction_payments_moderate')) {
            abort(403);
        }

        $auctionPayment->load(['auction.images', 'auction.category', 'winner', 'seller', 'trackingUpdates']);

        return view('admin.auction-payments.show', compact('auctionPayment'))->with('context', 'moderator');
    }

    public function releaseEscrow(AuctionPayment $auctionPayment)
    {
        if (! Auth::user()->hasAuctionPermission('auction_payments_moderate')) {
            abort(403);
        }

        if ($auctionPayment->escrow_status !== 'held') {
            return back()->with('error', 'Escrow is not in held status.');
        }

        if (! $auctionPayment->canRelease()) {
            return back()->with('error', 'Cannot release yet. Buyer and seller must confirm delivery.');
        }

        $auctionPayment->update([
            'escrow_status' => 'released',
            'released_at' => now(),
        ]);

        return back()->with('success', 'Escrow released. Send payout to seller PayPal: ' . ($auctionPayment->seller_paypal_email ?? $auctionPayment->seller->email ?? 'N/A'));
    }
}
