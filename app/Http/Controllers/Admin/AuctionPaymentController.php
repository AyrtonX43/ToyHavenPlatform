<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;

class AuctionPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionPayment::with(['auction', 'winner'])->orderByDesc('created_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'paid', 'held', 'released', 'refunded'])) {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20);

        return view('admin.auction-payments.index', compact('payments'));
    }

    public function release(AuctionPayment $payment)
    {
        if (! in_array($payment->status, ['paid', 'held'])) {
            return back()->with('error', 'Only paid/held payments can be released.');
        }
        if ($payment->delivery_status !== 'delivered' && $payment->delivery_status !== 'confirmed') {
            return back()->with('error', 'Buyer must confirm delivery before releasing escrow.');
        }
        $payment->update(['status' => 'released']);
        return back()->with('success', 'Escrow released to seller.');
    }
}
