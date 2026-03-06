<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    public function index(AuctionPayment $auctionPayment)
    {
        if ($auctionPayment->winner_id !== Auth::id()) {
            abort(403);
        }

        if ($auctionPayment->isPaid()) {
            return redirect()->route('auctions.history')->with('info', 'Payment already completed.');
        }

        $auctionPayment->load('auction');

        return view('auctions.payment', [
            'payment' => $auctionPayment,
            'auction' => $auctionPayment->auction,
        ]);
    }

    public function processPayMongo(Request $request, AuctionPayment $auctionPayment)
    {
        if ($auctionPayment->winner_id !== Auth::id()) {
            abort(403);
        }

        if (! $this->payMongo->isConfigured()) {
            return back()->with('error', 'QR Ph payment is not configured.');
        }

        $amount = (float) $auctionPayment->amount;
        $metadata = ['auction_payment_id' => (string) $auctionPayment->id];

        $intent = $this->payMongo->createPaymentIntent($amount, 'PHP', $metadata);

        if (! $intent) {
            return back()->with('error', 'Could not create payment.');
        }

        $clientKey = $intent['attributes']['client_key'] ?? null;
        if (! $clientKey) {
            return back()->with('error', 'Payment initialization failed.');
        }

        return view('auctions.payment', [
            'payment' => $auctionPayment,
            'auction' => $auctionPayment->auction,
            'client_key' => $clientKey,
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
        ]);
    }
}
