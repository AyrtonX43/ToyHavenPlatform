<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    public function show(AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->winner_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $auctionPayment->load(['auction.images', 'auction.category']);

        $publicKey = config('services.paymongo.public_key');
        $clientKey = null;

        if ($auctionPayment->payment_status === 'pending' && $auctionPayment->paymongo_payment_intent_id) {
            $fetched = $this->payMongo->getPaymentIntent($auctionPayment->paymongo_payment_intent_id);
            $intentStatus = $fetched['attributes']['status'] ?? 'unknown';

            if ($intentStatus === 'awaiting_payment_method') {
                $clientKey = $fetched['attributes']['client_key'] ?? null;
            }
        }

        return view('auctions.payment', [
            'payment' => $auctionPayment,
            'auction' => $auctionPayment->auction,
            'publicKey' => $publicKey,
            'clientKey' => $clientKey,
        ]);
    }

    public function process(Request $request, AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->winner_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($auctionPayment->payment_status !== 'pending') {
            return response()->json(['error' => 'Payment already processed.'], 400);
        }

        if ($auctionPayment->isPastDeadline()) {
            return response()->json(['error' => 'Payment deadline has passed.'], 400);
        }

        $paymentType = $request->input('payment_type', 'card');

        $request->validate([
            'payment_type' => 'in:card,qrph',
            'card_number' => 'required_if:payment_type,card|nullable|string',
            'exp_month' => 'required_if:payment_type,card|nullable|integer|between:1,12',
            'exp_year' => 'required_if:payment_type,card|nullable|integer',
            'cvc' => 'required_if:payment_type,card|nullable|string|min:3|max:4',
        ]);

        $intentId = $auctionPayment->paymongo_payment_intent_id;

        if (! $intentId) {
            $intent = $this->payMongo->createPaymentIntent(
                (float) $auctionPayment->total_amount,
                'PHP',
                [
                    'auction_payment_id' => (string) $auctionPayment->id,
                    'auction_id' => (string) $auctionPayment->auction_id,
                    'winner_id' => (string) $user->id,
                ]
            );

            if (! $intent) {
                return response()->json(['error' => 'Failed to create payment session.'], 500);
            }

            $intentId = $intent['id'];
            $auctionPayment->update(['paymongo_payment_intent_id' => $intentId]);
        }

        if ($paymentType === 'qrph') {
            $pmId = $this->payMongo->createQrphPaymentMethod($user->name, $user->email);
            if (! $pmId) {
                return response()->json(['error' => 'Failed to create QR Ph payment method.'], 500);
            }
            $paymentMethodId = $pmId;
        } else {
            $cardResult = $this->payMongo->createCardPaymentMethod(
                $request->input('card_number'),
                (int) $request->input('exp_month'),
                (int) $request->input('exp_year'),
                $request->input('cvc'),
                $user->name ?? 'Customer',
                $user->email ?? ''
            );
            if (! $cardResult['success']) {
                return response()->json(['error' => $cardResult['error'] ?? 'Failed to create card payment.'], 400);
            }
            $paymentMethodId = $cardResult['id'];
        }

        $returnUrl = url('/auctions/payment/' . $auctionPayment->id . '/check') . '?' . http_build_query([
            'payment_intent_id' => $intentId,
        ]);

        $result = $this->payMongo->attachPaymentMethod($intentId, $paymentMethodId, $returnUrl);

        if (! $result) {
            return response()->json(['error' => 'Failed to process payment.'], 500);
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;

        if ($status === 'succeeded') {
            $this->markAsPaid($auctionPayment);
            return response()->json(['status' => 'succeeded', 'redirect_url' => $returnUrl]);
        }

        if ($status === 'awaiting_next_action' && $nextAction) {
            $nextType = $nextAction['type'] ?? null;
            if ($nextType === 'consume_qr') {
                return response()->json([
                    'status' => 'awaiting_next_action',
                    'qr_image' => $nextAction['code']['image_url'] ?? null,
                    'payment_intent_id' => $intentId,
                ]);
            }

            $redirectUrl = $nextAction['redirect']['url'] ?? $nextAction['url'] ?? null;
            return response()->json([
                'status' => 'awaiting_next_action',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return response()->json(['error' => "Payment returned status: {$status}"], 400);
    }

    public function checkStatus(AuctionPayment $auctionPayment, Request $request)
    {
        $user = Auth::user();

        if ($auctionPayment->winner_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $intentId = $request->input('payment_intent_id', $auctionPayment->paymongo_payment_intent_id);

        if (! $intentId) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'unknown']);
            }
            return redirect()->route('auctions.payment.show', $auctionPayment)->with('error', 'No payment found.');
        }

        $intent = $this->payMongo->getPaymentIntent($intentId);
        $status = $intent['attributes']['status'] ?? 'unknown';

        if ($status === 'succeeded' && $auctionPayment->payment_status !== 'paid') {
            $this->markAsPaid($auctionPayment);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => $status]);
        }

        if ($status === 'succeeded') {
            return redirect()->route('auctions.payment.show', $auctionPayment)
                ->with('success', 'Payment successful! The seller will ship your item soon.');
        }

        return redirect()->route('auctions.payment.show', $auctionPayment)
            ->with('error', 'Payment not completed. Please try again.');
    }

    public function confirmReceived(AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->winner_id !== $user->id) {
            abort(403);
        }

        if ($auctionPayment->escrow_status !== 'held') {
            return back()->with('error', 'Cannot confirm receipt for this payment.');
        }

        $auctionPayment->update([
            'delivery_status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return back()->with('success', 'Order confirmed as received! Escrow will be released to the seller.');
    }

    private function markAsPaid(AuctionPayment $auctionPayment): void
    {
        $auctionPayment->update([
            'payment_status' => 'paid',
            'escrow_status' => 'held',
            'paid_at' => now(),
            'delivery_status' => 'pending_shipment',
        ]);
    }
}
