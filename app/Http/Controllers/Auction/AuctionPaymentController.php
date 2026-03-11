<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Notifications\PaymentReceivedSellerNotification;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Srmklive\PayPal\Services\PayPal as PayPalService;

class AuctionPaymentController extends Controller
{
    public function show(AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        if ($payment->isPaid()) {
            return redirect()->route('auction.payment.success', $payment)
                ->with('info', 'This payment has already been completed.');
        }

        $payment->load(['auction.images', 'winner']);

        $config = config('paypal', []);
        $mode = $config['mode'] ?? 'sandbox';
        $creds = $config[$mode] ?? $config['sandbox'] ?? [];
        $paypalClientId = $creds['client_id'] ?? env('PAYPAL_SANDBOX_CLIENT_ID') ?: env('PAYPAL_CLIENT_ID', '');
        $paypalDemoMode = (bool) ($config['demo_mode'] ?? true);

        $paymongoService = app(PayMongoService::class);
        $paymongoEnabled = $paymongoService->isConfigured();
        $paymongoPublicKey = config('services.paymongo.public_key', '');

        return view('auction.payment.show', compact(
            'payment',
            'paypalClientId',
            'paypalDemoMode',
            'paymongoEnabled',
            'paymongoPublicKey',
        ));
    }

    // ─── PayPal ───

    public function createPayPalOrder(Request $request)
    {
        $request->validate(['payment_id' => 'required|exists:auction_payments,id']);

        $payment = AuctionPayment::findOrFail($request->payment_id);

        if ($payment->winner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $payment->isPending()) {
            return response()->json(['error' => 'Payment already completed'], 400);
        }

        try {
            $config = config('paypal');
            $creds = $config[$config['mode'] ?? 'sandbox'] ?? $config['sandbox'] ?? [];
            if (empty($creds['client_id'] ?? '') || empty($creds['client_secret'] ?? '')) {
                return response()->json(['error' => 'PayPal is not configured'], 500);
            }

            $paypal = new PayPalService;
            $paypal->setApiCredentials($config);
            $paypal->getAccessToken();

            $currency = config('paypal.currency', 'PHP');
            $amount = number_format((float) $payment->amount, 2, '.', '');

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => ['currency_code' => $currency, 'value' => $amount],
                    'description' => 'Auction: ' . $payment->auction->title,
                    'custom_id' => (string) $payment->id,
                ]],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                    'landing_page' => 'LOGIN',
                ],
            ];

            $response = $paypal->createOrder($orderData);

            if (isset($response['error']) || ! is_array($response) || empty($response['id'])) {
                Log::error('Auction PayPal create order error', ['response' => $response ?? []]);
                return response()->json(['error' => 'Could not create PayPal order'], 500);
            }

            $orderId = $response['id'];
            Cache::put('auction_paypal_order_' . $orderId, $payment->id, 3600);

            return response()->json(['orderId' => $orderId]);
        } catch (\Throwable $e) {
            Log::error('Auction PayPal create order failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payment failed. Please try again.'], 500);
        }
    }

    public function capturePayPalOrder(Request $request)
    {
        $request->validate(['order_id' => 'required|string']);

        $orderId = $request->order_id;

        try {
            $config = config('paypal');
            $paypal = new PayPalService;
            $paypal->setApiCredentials($config);
            $paypal->getAccessToken();

            try {
                $response = $paypal->capturePaymentOrder($orderId);
            } catch (\Throwable $e) {
                Log::warning('Auction PayPal capture threw', ['orderId' => $orderId, 'error' => $e->getMessage()]);
                try {
                    $response = $paypal->showOrderDetails($orderId);
                } catch (\Throwable $e2) {
                    throw $e;
                }
            }

            if (isset($response['error']) || isset($response['message'])) {
                Log::error('Auction PayPal capture error', ['orderId' => $orderId, 'response' => $response]);
                return response()->json(['error' => 'Payment capture failed'], 400);
            }

            $status = $response['status'] ?? null;
            if ($status !== 'COMPLETED') {
                return response()->json(['error' => 'Payment was not completed'], 400);
            }

            $paymentId = null;
            foreach ($response['purchase_units'] ?? [] as $unit) {
                $cid = $unit['custom_id'] ?? null;
                if ($cid) {
                    $paymentId = (int) $cid;
                    break;
                }
            }

            if (! $paymentId) {
                $paymentId = Cache::get('auction_paypal_order_' . $orderId);
            }

            if (! $paymentId) {
                Log::error('Auction PayPal capture: no payment for order', ['orderId' => $orderId]);
                return response()->json(['error' => 'Invalid order'], 400);
            }

            Cache::forget('auction_paypal_order_' . $orderId);

            $payment = AuctionPayment::find($paymentId);
            if (! $payment || $payment->winner_id !== Auth::id()) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            if ($payment->isPaid()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('auction.payment.success', $payment),
                ]);
            }

            $payment->update([
                'status' => 'held',
                'payment_method' => 'paypal',
                'payment_reference' => $orderId,
                'paid_at' => now(),
            ]);

            $redirectUrl = route('auction.payment.success', $payment);

            $this->notifySellerOfPayment($payment);

            return response()->json([
                'success' => true,
                'redirect' => $redirectUrl,
                'message' => 'Payment successful! The seller will ship your item soon.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Auction PayPal capture failed', ['error' => $e->getMessage(), 'orderId' => $orderId]);

            if (isset($payment) && $payment->isPaid()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('auction.payment.success', $payment),
                ]);
            }

            return response()->json(['error' => 'Payment failed. Please try again.'], 400);
        }
    }

    // ─── PayMongo ───

    public function createPayMongoIntent(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:auction_payments,id',
            'payment_type' => 'required|in:qrph',
        ]);

        $payment = AuctionPayment::findOrFail($request->payment_id);

        if ($payment->winner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $payment->isPending()) {
            return response()->json(['error' => 'Payment already completed'], 400);
        }

        $paymongo = app(PayMongoService::class);
        if (! $paymongo->isConfigured()) {
            return response()->json(['error' => 'PayMongo is not configured'], 500);
        }

        $payment->load('auction');

        $intent = $paymongo->createPaymentIntent(
            amount: (float) $payment->amount,
            currency: 'PHP',
            metadata: [
                'auction_payment_id' => $payment->id,
                'auction_id' => $payment->auction_id,
                'auction_title' => $payment->auction->title ?? '',
            ],
        );

        if (! $intent) {
            return response()->json(['error' => 'Could not create payment intent'], 500);
        }

        $payment->update(['payment_reference' => $intent['id']]);

        return response()->json([
            'client_key' => $intent['attributes']['client_key'] ?? null,
            'payment_intent_id' => $intent['id'],
        ]);
    }

    public function processPayMongoPayment(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|exists:auction_payments,id',
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
        ]);

        $payment = AuctionPayment::findOrFail($request->payment_id);

        if ($payment->winner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($payment->isPaid()) {
            return response()->json([
                'success' => true,
                'redirect' => route('auction.payment.success', $payment),
            ]);
        }

        $paymongo = app(PayMongoService::class);
        $returnUrl = route('auction.payment.paymongo.return', $payment);

        $result = $paymongo->attachPaymentMethod(
            $request->payment_intent_id,
            $request->payment_method_id,
            $returnUrl,
        );

        if (! $result) {
            return response()->json(['error' => 'Payment processing failed'], 400);
        }

        $status = $result['attributes']['status'] ?? '';
        $nextAction = $result['attributes']['next_action'] ?? null;

        if ($status === 'succeeded') {
            $payment->update([
                'status' => 'held',
                'payment_method' => 'paymongo_qrph',
                'payment_reference' => $request->payment_intent_id,
                'paid_at' => now(),
            ]);

            $this->notifySellerOfPayment($payment);

            return response()->json([
                'success' => true,
                'redirect' => route('auction.payment.success', $payment),
            ]);
        }

        if ($status === 'awaiting_next_action' && $nextAction) {
            $redirectUrl = $nextAction['redirect']['url'] ?? null;
            if ($redirectUrl) {
                return response()->json([
                    'requires_action' => true,
                    'redirect_url' => $redirectUrl,
                ]);
            }
        }

        return response()->json(['error' => 'Unexpected payment status: ' . $status], 400);
    }

    public function paymongoReturn(AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        if ($payment->isPaid()) {
            return redirect()->route('auction.payment.success', $payment)
                ->with('success', 'Payment completed!');
        }

        $intentId = $payment->payment_reference;
        if ($intentId) {
            $paymongo = app(PayMongoService::class);
            $intent = $paymongo->getPaymentIntent($intentId);
            $status = $intent['attributes']['status'] ?? '';

            if ($status === 'succeeded') {
                $payment->update([
                    'status' => 'held',
                    'payment_method' => 'paymongo_qrph',
                    'paid_at' => now(),
                ]);
                $this->notifySellerOfPayment($payment);
                return redirect()->route('auction.payment.success', $payment)
                    ->with('success', 'Payment completed!');
            }
        }

        return redirect()->route('auction.payment.show', $payment)
            ->with('error', 'Payment was not completed. Please try again.');
    }

    public function checkPayMongoStatus(Request $request)
    {
        $request->validate(['payment_id' => 'required|exists:auction_payments,id']);

        $payment = AuctionPayment::findOrFail($request->payment_id);

        if ($payment->winner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($payment->isPaid()) {
            return response()->json([
                'status' => 'paid',
                'redirect' => route('auction.payment.success', $payment),
            ]);
        }

        $intentId = $payment->payment_reference;
        if (! $intentId) {
            return response()->json(['status' => 'pending']);
        }

        $paymongo = app(PayMongoService::class);
        $intent = $paymongo->getPaymentIntent($intentId);
        $status = $intent['attributes']['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            $payment->update([
                'status' => 'held',
                'payment_method' => 'paymongo_qrph',
                'paid_at' => now(),
            ]);
            $this->notifySellerOfPayment($payment);
            return response()->json([
                'status' => 'paid',
                'redirect' => route('auction.payment.success', $payment),
            ]);
        }

        return response()->json(['status' => $status]);
    }

    // ─── Success & Fulfillment ───

    public function success(AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        $payment->load(['auction.images', 'auction.user', 'winner']);

        return view('auction.payment.success', compact('payment'));
    }

    public function markShipped(Request $request, AuctionPayment $payment)
    {
        $user = Auth::user();
        $payment->load('auction');

        if (! $payment->auction) {
            return back()->with('error', 'Auction not found.');
        }
        if ($payment->auction->user_id !== $user->id) {
            abort(403);
        }
        if (! $payment->isPaid()) {
            return back()->with('error', 'Payment must be completed first.');
        }
        if ($payment->isShipped()) {
            return back()->with('info', 'This item has already been marked as shipped.');
        }

        $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'carrier' => 'nullable|string|max:100',
        ]);

        try {
            $data = ['delivery_status' => 'shipped'];
            if (Schema::hasColumn('auction_payments', 'tracking_number')) {
                $data['tracking_number'] = $request->filled('tracking_number') ? $request->tracking_number : null;
            }
            if (Schema::hasColumn('auction_payments', 'shipped_at')) {
                $data['shipped_at'] = now();
            }
            $payment->update($data);

            return back()->with('success', 'Marked as shipped.');
        } catch (\Throwable $e) {
            Log::error('Auction markShipped failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Could not update. Please try again or contact support.');
        }
    }

    public function confirmDelivery(AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        if ($payment->delivery_status !== 'shipped') {
            return back()->with('error', 'This item has not been shipped yet.');
        }

        $payment->update([
            'delivery_status' => 'delivered',
            'confirmed_at' => now(),
        ]);
        return back()->with('success', 'Delivery confirmed. Thank you!');
    }

    private function notifySellerOfPayment(AuctionPayment $payment): void
    {
        try {
            $payment->load('auction.user');
            $seller = $payment->auction->user;
            if ($seller) {
                $seller->notify(new PaymentReceivedSellerNotification($payment->auction, $payment));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to notify seller of payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function downloadReceipt(AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->winner_id !== $user->id && $payment->auction->user_id !== $user->id) {
            abort(403);
        }

        if (! $payment->isPaid()) {
            return back()->with('error', 'Receipt is only available after payment.');
        }

        $payment->load(['auction.images', 'winner', 'auction.user']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('auction.payment.receipt-pdf', compact('payment'));

        return $pdf->download('auction-receipt-' . $payment->id . '.pdf');
    }
}
