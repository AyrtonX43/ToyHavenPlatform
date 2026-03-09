<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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

        $payment->load(['auction', 'winner']);

        $config = config('paypal', []);
        $mode = $config['mode'] ?? 'sandbox';
        $creds = $config[$mode] ?? $config['sandbox'] ?? [];
        $paypalClientId = $creds['client_id'] ?? env('PAYPAL_SANDBOX_CLIENT_ID') ?: env('PAYPAL_CLIENT_ID', '');
        $paypalDemoMode = (bool) ($config['demo_mode'] ?? true);

        return view('auction.payment.show', compact('payment', 'paypalClientId', 'paypalDemoMode'));
    }

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

            return response()->json([
                'success' => true,
                'redirect' => route('auction.payment.success', $payment),
                'message' => 'Payment successful! The seller will ship your item soon.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Auction PayPal capture failed', ['error' => $e->getMessage(), 'orderId' => $orderId]);
            return response()->json(['error' => 'Payment failed. Please try again.'], 400);
        }
    }

    public function success(AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        $payment->load(['auction']);

        return view('auction.payment.success', compact('payment'));
    }

    public function markShipped(Request $request, AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->auction->user_id !== $user->id) {
            abort(403);
        }
        if (! $payment->isPaid()) {
            return back()->with('error', 'Payment must be completed first.');
        }
        $payment->update([
            'delivery_status' => 'shipped',
            'tracking_number' => $request->filled('tracking_number') ? $request->tracking_number : null,
        ]);
        return back()->with('success', 'Marked as shipped.');
    }

    public function confirmDelivery(AuctionPayment $payment)
    {
        $user = Auth::user();
        if ($payment->winner_id !== $user->id) {
            abort(403);
        }
        $payment->update([
            'delivery_status' => 'delivered',
            'confirmed_at' => now(),
        ]);
        return back()->with('success', 'Delivery confirmed. Thank you!');
    }
}
