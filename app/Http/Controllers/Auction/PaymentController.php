<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionPayment;
use App\Models\AuctionSecondChance;
use App\Services\AuctionReceiptService;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo,
        protected AuctionReceiptService $receiptService
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

    public function showSecondChance(Auction $auction)
    {
        $user = Auth::user();

        $secondChance = AuctionSecondChance::where('auction_id', $auction->id)
            ->where('user_id', $user->id)
            ->where('status', 'offered')
            ->where('deadline', '>', now())
            ->first();

        if (! $secondChance) {
            abort(404, 'Second chance offer not found or expired.');
        }

        $payment = AuctionPayment::where('auction_id', $auction->id)
            ->where('winner_id', $user->id)
            ->where('is_second_chance', true)
            ->first();

        if (! $payment) {
            $payment = DB::transaction(function () use ($auction, $secondChance, $user) {
                $plan = $user->currentPlan();
                $buyerPremiumRate = $plan ? $plan->getBuyersPremiumRate() : 5;
                $buyerPremium = round($secondChance->bid_amount * ($buyerPremiumRate / 100), 2);
                $totalAmount = $secondChance->bid_amount + $buyerPremium;
                $platformFee = round($totalAmount * 0.05, 2);
                $sellerPayout = $secondChance->bid_amount - $platformFee;
                $seller = $auction->getSellerUser();

                return AuctionPayment::create([
                    'auction_id' => $auction->id,
                    'winner_id' => $user->id,
                    'seller_user_id' => $seller->id,
                    'seller_paypal_email' => $auction->auctionSellerProfile?->paypal_email ?? $seller->email,
                    'bid_amount' => $secondChance->bid_amount,
                    'buyer_premium' => $buyerPremium,
                    'total_amount' => $totalAmount,
                    'platform_fee' => $platformFee,
                    'seller_payout' => $sellerPayout,
                    'payment_status' => 'pending',
                    'escrow_status' => 'awaiting_payment',
                    'payment_deadline' => $secondChance->deadline,
                    'is_second_chance' => true,
                ]);
            });
        }

        return $this->show($payment);
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

        $paymentType = $request->input('payment_type', 'qrph');

        $request->validate([
            'payment_type' => 'in:qrph,paypal_demo',
        ]);

        if ($paymentType === 'paypal_demo') {
            $auctionPayment->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'paypal_demo',
                'escrow_status' => 'held',
            ]);

            $this->completeSecondChanceIfApplicable($auctionPayment);

            try {
                $this->receiptService->generateReceipt($auctionPayment);
            } catch (\Throwable $e) {
                Log::warning('Auction receipt generation failed', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'succeeded',
                'redirect_url' => route('auctions.payment.success', $auctionPayment),
            ]);
        }

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

        $pmId = $this->payMongo->createQrphPaymentMethod($user->name, $user->email);
        if (! $pmId) {
            return response()->json(['error' => 'Failed to create QR Ph payment method.'], 500);
        }

        $returnUrl = url('/auctions/payment/' . $auctionPayment->id . '/check') . '?' . http_build_query(['payment_intent_id' => $intentId]);
        $result = $this->payMongo->attachPaymentMethod($intentId, $pmId, $returnUrl);

        if (! $result) {
            return response()->json(['error' => 'Failed to attach payment method.'], 500);
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;

        if ($status === 'succeeded') {
            return response()->json(['status' => 'succeeded', 'redirect_url' => $returnUrl]);
        }

        if ($status === 'awaiting_next_action' && $nextAction && ($nextAction['type'] ?? '') === 'consume_qr') {
            return response()->json([
                'status' => 'awaiting_next_action',
                'qr_image' => $nextAction['code']['image_url'] ?? null,
                'payment_intent_id' => $intentId,
            ]);
        }

        return response()->json(['status' => 'processing', 'redirect_url' => $returnUrl]);
    }

    public function checkStatus(Request $request, AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->winner_id !== $user->id) {
            abort(403);
        }

        $intentId = $auctionPayment->paymongo_payment_intent_id;
        if (! $intentId) {
            return response()->json(['status' => 'awaiting_payment_method']);
        }

        $intent = $this->payMongo->getPaymentIntent($intentId);
        $status = $intent['attributes']['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            $auctionPayment->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'qrph',
                'escrow_status' => 'held',
            ]);

            $this->completeSecondChanceIfApplicable($auctionPayment);

            try {
                $this->receiptService->generateReceipt($auctionPayment);
            } catch (\Throwable $e) {
                Log::warning('Auction receipt generation failed', ['error' => $e->getMessage()]);
            }

            return redirect()->route('auctions.payment.success', $auctionPayment);
        }

        return response()->json(['status' => $status]);
    }

    public function success(AuctionPayment $auctionPayment)
    {
        if ($auctionPayment->winner_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $auctionPayment->load('auction');

        return view('auctions.payment-success', compact('auctionPayment'));
    }

    public function confirmReceived(Request $request, AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->winner_id !== $user->id) {
            return back()->with('error', 'Unauthorized.');
        }

        $request->validate(['proof' => 'required|file|image|max:5120']);

        $path = $request->file('proof')->store('auction_proofs/' . $auctionPayment->id, 'public');

        $auctionPayment->update([
            'winner_proof_path' => $path,
            'winner_received_confirmed_at' => now(),
            'delivery_status' => 'confirmed',
        ]);

        return back()->with('success', 'Thank you for confirming receipt.');
    }

    public function confirmSellerDelivery(Request $request, AuctionPayment $auctionPayment)
    {
        $user = Auth::user();

        if ($auctionPayment->seller_user_id !== $user->id) {
            return back()->with('error', 'Unauthorized.');
        }

        if ($auctionPayment->payment_status !== 'paid') {
            return back()->with('error', 'Payment is not complete.');
        }

        $auctionPayment->update([
            'seller_delivery_confirmed_at' => now(),
            'delivery_status' => 'confirmed',
        ]);

        return back()->with('success', 'You have confirmed delivery.');
    }

    protected function completeSecondChanceIfApplicable(AuctionPayment $auctionPayment): void
    {
        if (! $auctionPayment->is_second_chance) {
            return;
        }

        DB::transaction(function () use ($auctionPayment) {
            $auctionPayment->auction->update([
                'winner_id' => $auctionPayment->winner_id,
                'winning_amount' => $auctionPayment->bid_amount,
            ]);

            AuctionSecondChance::where('auction_id', $auctionPayment->auction_id)
                ->where('user_id', $auctionPayment->winner_id)
                ->update(['status' => 'accepted']);

            AuctionSecondChance::where('auction_id', $auctionPayment->auction_id)
                ->where('user_id', '!=', $auctionPayment->winner_id)
                ->update(['status' => 'expired']);
        });
    }
}
