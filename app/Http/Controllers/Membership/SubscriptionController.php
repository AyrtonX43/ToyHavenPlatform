<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Srmklive\PayPal\Services\PayPal as PayPalService;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    /**
     * Manage subscription page
     */
    public function manage()
    {
        $user = Auth::user();
        $activeSubscription = $user->activeSubscription();
        $subscriptions = $user->subscriptions()->with(['plan', 'payments'])->orderByDesc('created_at')->get();

        return view('membership.manage', [
            'activeSubscription' => $activeSubscription,
            'subscriptions' => $subscriptions,
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Accept terms and redirect to payment selection
     */
    public function acceptTerms(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'terms_accepted' => 'required|accepted',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        return redirect()->route('membership.payment-selection', $plan->slug);
    }

    /**
     * Subscribe to a plan (redirect to payment)
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'terms_accepted' => 'required|accepted',
            'payment_method' => 'required|in:qrph,paypal',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        if (Auth::user()->hasActiveMembership()) {
            return redirect()->route('membership.manage')
                ->with('info', 'You already have an active membership. Upgrade or cancel from Manage.');
        }

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
            'current_period_start' => null,
            'current_period_end' => null,
        ]);

        session(['membership_payment_method' => $request->payment_method]);

        return redirect()->route('membership.payment', ['subscription' => $subscription->id]);
    }

    /**
     * Payment page for a subscription
     */
    public function payment(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if ($subscription->status === 'active' && $subscription->payments()->where('status', 'paid')->exists()) {
            return redirect()->route('membership.manage');
        }

        $subscription->load('plan');

        // PayPal: redirect directly to PayPal
        $method = session('membership_payment_method', 'qrph');
        if ($method === 'paypal') {
            return $this->processPayPal($subscription);
        }

        // QRPH: generate and show QR code
        return $this->processPayMongo($subscription);
    }

    /**
     * Process payment (QR Ph via PayMongo or PayPal - stubbed for Phase 1)
     */
    public function processPayment(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'payment_method' => 'required|in:qrph,paypal',
        ]);

        $paymentMethod = $request->payment_method;

        if ($paymentMethod === 'qrph') {
            return $this->processPayMongo($subscription);
        }

        if ($paymentMethod === 'paypal') {
            return $this->processPayPal($subscription);
        }

        return back()->with('error', 'Invalid payment method.');
    }

    protected function processPayMongo(Subscription $subscription)
    {
        if (! $this->payMongo->isConfigured()) {
            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'QR Ph payment is not configured. Please contact support.');
        }

        $amount = (float) $subscription->plan->price;
        $metadata = ['subscription_id' => (string) $subscription->id];

        $intent = $this->payMongo->createPaymentIntent($amount, 'PHP', $metadata);

        if (! $intent) {
            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'Could not create payment. Please try again.');
        }

        $user = Auth::user();
        $pmId = $this->payMongo->createQrphPaymentMethod($user->name, $user->email);
        if (! $pmId) {
            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'Could not initialize QR Ph. Please try again.');
        }

        $returnUrl = url('/membership/payment-return') . '?' . http_build_query([
            'subscription_id' => $subscription->id,
        ]);

        $result = $this->payMongo->attachPaymentMethod($intent['id'], $pmId, $returnUrl);
        if (! $result) {
            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'Payment initialization failed. Please try again.');
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;
        $qrImage = null;
        if ($status === 'awaiting_next_action' && $nextAction && ($nextAction['type'] ?? '') === 'consume_qr') {
            $qrImage = $nextAction['code']['image_url'] ?? null;
        }

        if (! $qrImage) {
            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'Could not generate QR code. Please try again.');
        }

        return view('membership.payment', [
            'subscription' => $subscription,
            'qr_image' => $qrImage,
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
        ]);
    }

    protected function processPayPal(Subscription $subscription)
    {
        try {
            $config = config('paypal');
            $mode = $config['mode'] ?? 'sandbox';
            $creds = $config[$mode] ?? $config['sandbox'] ?? [];
            $clientId = $creds['client_id'] ?? '';
            $clientSecret = $creds['client_secret'] ?? '';

            if (empty($clientId) || empty($clientSecret)) {
                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('error', 'PayPal is not configured. Add PAYPAL_SANDBOX_CLIENT_ID and PAYPAL_SANDBOX_CLIENT_SECRET to .env, then run: php artisan config:clear');
            }

            $paypal = new PayPalService;
            $paypal->setApiCredentials($config);

            // Required: get access token before any API call (per srmklive docs)
            $paypal->getAccessToken();
        } catch (\Throwable $e) {
            Log::error('PayPal init failed', ['error' => $e->getMessage()]);

            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'PayPal could not be initialized. Please try again or use QR Ph.');
        }

        $returnUrl = route('membership.paypal.return');
        $cancelUrl = route('membership.paypal.cancel');
        $currency = config('paypal.currency', 'PHP');
        $amount = number_format($subscription->plan->price, 2, '.', '');

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $amount,
                ],
                'description' => $subscription->plan->name . ' Membership',
                'custom_id' => (string) $subscription->id,
            ]],
            'application_context' => [
                'return_url' => $returnUrl . '?subscription_id=' . $subscription->id,
                'cancel_url' => $cancelUrl . '?subscription_id=' . $subscription->id,
                'brand_name' => config('app.name'),
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'landing_page' => 'LOGIN',
            ],
        ];

        try {
            $response = $paypal->createOrder($orderData);

            if (isset($response['error'])) {
                Log::error('PayPal create order error', ['response' => $response, 'subscription' => $subscription->id]);

                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('error', 'PayPal could not create the order. Please try again or use QR Ph.');
            }

            // Handle API error responses (e.g. validation, invalid credentials)
            if (! is_array($response)) {
                Log::error('PayPal create order invalid response', ['response' => $response, 'subscription' => $subscription->id]);

                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('error', 'PayPal could not create the order. Please try again or use QR Ph.');
            }

            if (isset($response['id']) && ($response['status'] ?? '') === 'CREATED') {
                foreach ($response['links'] ?? [] as $link) {
                    if (($link['rel'] ?? '') === 'approve') {
                        session(['paypal_subscription_id' => $subscription->id]);

                        return redirect()->away($link['href']);
                    }
                }
            }

            // Log unexpected success response structure
            Log::warning('PayPal create order missing approve link', ['response' => $response, 'subscription' => $subscription->id]);
        } catch (\Throwable $e) {
            Log::error('PayPal create order failed', [
                'error' => $e->getMessage(),
                'subscription' => $subscription->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return redirect()->route('membership.payment-selection', $subscription->plan->slug)
            ->with('error', 'PayPal could not create the order. Please try again or use QR Ph.');
    }

    /**
     * API: Create PayPal order (for popup / Smart Buttons)
     */
    public function createPayPalOrder(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        if (Auth::user()->hasActiveMembership()) {
            return response()->json(['error' => 'Already have active membership'], 400);
        }

        $subscription = Subscription::create([
            'user_id' => Auth::id(),
            'plan_id' => $plan->id,
            'status' => 'pending',
            'current_period_start' => null,
            'current_period_end' => null,
        ]);

        try {
            $config = config('paypal');
            $creds = $config[$config['mode'] ?? 'sandbox'] ?? $config['sandbox'] ?? [];
            if (empty($creds['client_id'] ?? '') || empty($creds['client_secret'] ?? '')) {
                return response()->json(['error' => 'PayPal not configured'], 500);
            }

            $paypal = new PayPalService;
            $paypal->setApiCredentials($config);
            $paypal->getAccessToken();

            $currency = config('paypal.currency', 'PHP');
            $amount = number_format($subscription->plan->price, 2, '.', '');

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => ['currency_code' => $currency, 'value' => $amount],
                    'description' => $subscription->plan->name . ' Membership',
                    'custom_id' => (string) $subscription->id,
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
                Log::error('PayPal create order API error', ['response' => $response ?? []]);

                return response()->json(['error' => 'Could not create PayPal order'], 500);
            }

            $orderId = $response['id'];
            Cache::put('paypal_order_' . $orderId, $subscription->id, 3600);

            return response()->json([
                'orderId' => $orderId,
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayPal create order failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Payment failed. Please try again.'], 500);
        }
    }

    /**
     * API: Capture PayPal order (for popup / Smart Buttons)
     */
    public function capturePayPalOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $orderId = $request->order_id;
        $response = null;

        try {
            $config = config('paypal');
            $paypal = new PayPalService;
            $paypal->setApiCredentials($config);
            $paypal->getAccessToken();

            try {
                $response = $paypal->capturePaymentOrder($orderId);
            } catch (\Throwable $captureEx) {
                Log::warning('PayPal capture threw, trying showOrderDetails', ['orderId' => $orderId, 'error' => $captureEx->getMessage()]);
                try {
                    $response = $paypal->showOrderDetails($orderId);
                } catch (\Throwable $showEx) {
                    Log::error('PayPal showOrderDetails also failed', ['orderId' => $orderId, 'error' => $showEx->getMessage()]);
                    throw $captureEx;
                }
            }

            Log::info('PayPal capture response', ['orderId' => $orderId, 'status' => $response['status'] ?? null, 'has_purchase_units' => isset($response['purchase_units'])]);

            if (isset($response['error']) || isset($response['message'])) {
                Log::error('PayPal capture API error', ['orderId' => $orderId, 'response' => $response]);
                $errMsg = $response['message'] ?? $response['error']['message'] ?? $response['error'] ?? 'Payment capture failed';
                return response()->json(['error' => is_string($errMsg) ? $errMsg : 'Payment capture failed'], 400);
            }

            $status = $response['status'] ?? null;
            if ($status !== 'COMPLETED') {
                return response()->json(['error' => 'Payment was not completed'], 400);
            }

            $subscriptionId = null;
            foreach ($response['purchase_units'] ?? [] as $unit) {
                $cid = $unit['custom_id'] ?? null;
                if ($cid) {
                    $subscriptionId = (int) $cid;
                    break;
                }
            }

            if (! $subscriptionId) {
                $subscriptionId = Cache::get('paypal_order_' . $orderId);
            }

            if (! $subscriptionId) {
                Log::error('PayPal capture: no subscription for order', ['orderId' => $orderId, 'response_keys' => array_keys($response ?? [])]);

                return response()->json(['error' => 'Invalid order'], 400);
            }

            Cache::forget('paypal_order_' . $orderId);

            $subscription = Subscription::find($subscriptionId);
            if (! $subscription || $subscription->user_id !== Auth::id()) {
                return response()->json(['error' => 'Subscription not found'], 404);
            }

            $refCol = Schema::hasColumn('subscription_payments', 'payment_reference') ? 'payment_reference' : 'paymongo_payment_id';
            if ($subscription->payments()->where('status', 'paid')->where($refCol, $orderId)->exists()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('membership.payment-success', $subscription),
                    'message' => 'Payment successful! Your ' . $subscription->plan->name . ' membership is now active.',
                ]);
            }

            $amount = 0;
            foreach ($response['purchase_units'] ?? [] as $unit) {
                foreach ($unit['payments']['captures'] ?? [] as $cap) {
                    $amount += (float) ($cap['amount']['value'] ?? 0);
                }
            }
            $amount = $amount ?: $subscription->plan->price;

            DB::transaction(function () use ($subscription, $orderId, $amount) {
                $subscription->update([
                    'status' => 'active',
                    'payment_method' => 'paypal',
                    'current_period_start' => now(),
                    'current_period_end' => ($subscription->plan?->interval ?? 'month') === 'year'
                        ? now()->addYear()
                        : now()->addMonth(),
                ]);

                $paymentData = [
                    'subscription_id' => $subscription->id,
                    'amount' => $amount,
                    'status' => 'paid',
                    'paid_at' => now(),
                ];
                if (Schema::hasColumn('subscription_payments', 'payment_reference')) {
                    $paymentData['payment_reference'] = $orderId;
                }
                if (Schema::hasColumn('subscription_payments', 'paymongo_payment_id') && ! isset($paymentData['payment_reference'])) {
                    $paymentData['paymongo_payment_id'] = $orderId;
                }
                if (Schema::hasColumn('subscription_payments', 'payment_method')) {
                    $paymentData['payment_method'] = 'paypal';
                }

                SubscriptionPayment::create($paymentData);
            });

            $subscriptionPayment = $subscription->payments()->where('status', 'paid')->latest()->first();
            if ($subscriptionPayment) {
                $receiptService = app(\App\Services\SubscriptionReceiptService::class);
                $receiptService->generateReceipt($subscriptionPayment);
                $subscription->user->notify(new \App\Notifications\MembershipPaymentSuccessNotification($subscriptionPayment));
            }

            return response()->json([
                'success' => true,
                'redirect' => route('membership.payment-success', $subscription),
                'message' => 'Payment successful! Your ' . $subscription->plan->name . ' membership is now active. Receipt has been sent to your email.',
            ]);
        } catch (\Throwable $e) {
            Log::error('PayPal capture failed', [
                'error' => $e->getMessage(),
                'orderId' => $orderId,
                'trace' => $e->getTraceAsString(),
                'class' => get_class($e),
            ]);

            return response()->json(['error' => 'Payment failed. Please try again.'], 400);
        }
    }

    /**
     * PayPal return (after user approves payment - redirect flow fallback)
     */
    public function paypalReturn(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');
        $token = $request->query('token'); // PayPal order ID

        if (! $subscriptionId || ! $token) {
            return redirect()->route('auctions.index')->with('error', 'Invalid PayPal return.');
        }

        $subscription = Subscription::find($subscriptionId);
        if (! $subscription || $subscription->user_id !== Auth::id()) {
            return redirect()->route('auctions.index')->with('error', 'Subscription not found.');
        }

        try {
            $config = config('paypal');
            $paypal = new PayPalService;
            $paypal->setApiCredentials($config);
            $paypal->getAccessToken();
            $response = $paypal->capturePaymentOrder($token);

            $status = $response['status'] ?? null;
            if ($status === 'COMPLETED') {
                $amount = 0;
                foreach ($response['purchase_units'] ?? [] as $unit) {
                    $payments = $unit['payments']['captures'] ?? [];
                    foreach ($payments as $cap) {
                        $amount += (float) ($cap['amount']['value'] ?? 0);
                    }
                }

                $subscription->update([
                    'status' => 'active',
                    'payment_method' => 'paypal',
                    'current_period_start' => now(),
                    'current_period_end' => ($subscription->plan?->interval ?? 'month') === 'year'
                        ? now()->addYear()
                        : now()->addMonth(),
                ]);

                $subscriptionPayment = SubscriptionPayment::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $amount ?: $subscription->plan->price,
                    'payment_reference' => $token,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'paypal',
                ]);

                $receiptService = app(\App\Services\SubscriptionReceiptService::class);
                $receiptService->generateReceipt($subscriptionPayment);

                $subscription->user->notify(new \App\Notifications\MembershipPaymentSuccessNotification($subscriptionPayment));

                return redirect()->route('membership.payment-success', $subscription)
                    ->with('success', 'Payment successful! Your ' . $subscription->plan->name . ' membership is now active. Receipt has been sent to your email.');
            }
        } catch (\Throwable $e) {
            Log::error('PayPal capture failed', ['error' => $e->getMessage(), 'token' => $token]);
        }

        return redirect()->route('membership.payment-selection', $subscription->plan->slug)
            ->with('payment_failed', true);
    }

    /**
     * PayPal demo payment - simulated success for demo mode
     */
    /**
     * PayPal demo page - opens in popup, simulates PayPal checkout
     */
    public function paypalDemoPage(Request $request)
    {
        $planId = $request->query('plan_id');
        $plan = Plan::where('id', $planId)->where('is_active', true)->firstOrFail();

        return view('membership.paypal-demo', [
            'plan' => $plan,
        ]);
    }

    public function paypalDemoPay(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $plan = Plan::findOrFail($request->plan_id);

        if (Auth::user()->hasActiveMembership()) {
            return redirect()->route('membership.manage')
                ->with('info', 'You already have an active membership.');
        }

        $amount = (float) $plan->price;
        $reference = 'DEMO-PAYPAL-' . uniqid();

        try {
            $subscription = DB::transaction(function () use ($plan, $amount, $reference) {
                $subscription = Subscription::create([
                    'user_id' => Auth::id(),
                    'plan_id' => $plan->id,
                    'status' => 'pending',
                    'current_period_start' => null,
                    'current_period_end' => null,
                ]);

                $paymentData = [
                    'subscription_id' => $subscription->id,
                    'amount' => $amount,
                    'status' => 'paid',
                    'paid_at' => now(),
                ];
                if (Schema::hasColumn('subscription_payments', 'payment_reference')) {
                    $paymentData['payment_reference'] = $reference;
                }
                if (Schema::hasColumn('subscription_payments', 'paymongo_payment_id') && ! isset($paymentData['payment_reference'])) {
                    $paymentData['paymongo_payment_id'] = $reference;
                }
                if (Schema::hasColumn('subscription_payments', 'payment_method')) {
                    $paymentData['payment_method'] = 'paypal';
                }

                SubscriptionPayment::create($paymentData);

                $subscription->update([
                    'status' => 'active',
                    'payment_method' => 'paypal',
                    'current_period_start' => now(),
                    'current_period_end' => ($plan->interval ?? 'month') === 'year'
                        ? now()->addYear()
                        : now()->addMonth(),
                ]);

                return $subscription->fresh();
            });

            $subscriptionPayment = $subscription->payments()->where('status', 'paid')->latest()->first();
            if ($subscriptionPayment) {
                try {
                    $receiptService = app(\App\Services\SubscriptionReceiptService::class);
                    $receiptService->generateReceipt($subscriptionPayment);
                    $subscription->user->notify(new \App\Notifications\MembershipPaymentSuccessNotification($subscriptionPayment));
                } catch (\Throwable $e) {
                    Log::warning('PayPal demo: receipt/notification failed', ['error' => $e->getMessage()]);
                }
            }

            $successUrl = route('membership.payment-success', $subscription);
            return redirect($successUrl . '?popup=1')
                ->with('success', 'Payment successful! Your membership is now active. Receipt has been sent to your email.');
        } catch (\Throwable $e) {
            Log::error('PayPal demo payment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('membership.payment-selection', $plan->slug)
                ->with('error', 'Payment could not be processed. Please try again.');
        }
    }

    /**
     * PayPal cancel (user cancelled on PayPal)
     */
    public function paypalCancel(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');
        if ($subscriptionId) {
            $subscription = Subscription::find($subscriptionId);
            if ($subscription && $subscription->user_id === Auth::id() && $subscription->status === 'pending') {
                $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('info', 'Payment was cancelled. You can try again or choose another payment method.');
            }
        }

        return redirect()->route('membership.index')->with('info', 'Payment cancelled. You can select a plan when ready.');
    }

    /**
     * Payment return (after PayMongo redirect)
     */
    public function paymentReturn(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');

        if (! $subscriptionId) {
            return redirect()->route('membership.index')->with('error', 'Invalid payment return.');
        }

        $subscription = Subscription::find($subscriptionId);

        if (! $subscription || $subscription->user_id !== Auth::id()) {
            return redirect()->route('membership.index')->with('error', 'Subscription not found.');
        }

        return redirect()->route('membership.payment-success', $subscription)
            ->with('success', 'Payment successful! Your membership is now active. Receipt has been sent to your email.');
    }

    /**
     * Payment success page (after webhook confirms - or for demo)
     */
    public function paymentSuccess(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $subscription->load('plan');

        return view('membership.payment-success', [
            'subscription' => $subscription,
        ]);
    }

    /**
     * Check payment status (for polling)
     */
    public function checkPaymentStatus(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $paid = $subscription->payments()->where('status', 'paid')->exists();

        return response()->json([
            'paid' => $paid,
            'redirect' => $paid ? route('membership.payment-success', $subscription) : null,
        ]);
    }

    /**
     * Download receipt for a subscription payment
     */
    public function downloadReceipt(Subscription $subscription, SubscriptionPayment $subscriptionPayment)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if ($subscriptionPayment->subscription_id !== $subscription->id) {
            abort(404);
        }

        if (! $subscriptionPayment->hasReceipt()) {
            return redirect()->route('membership.manage')->with('error', 'Receipt not available.');
        }

        $receiptService = app(\App\Services\SubscriptionReceiptService::class);

        return $receiptService->downloadReceipt($subscriptionPayment);
    }

    /**
     * Cancel active subscription
     */
    public function cancel(Request $request)
    {
        $user = Auth::user();
        $activeSubscription = $user->activeSubscription();

        if (! $activeSubscription) {
            return redirect()->route('membership.manage')->with('error', 'No active subscription found.');
        }

        $deactivateShop = $request->boolean('deactivate_shop') && $user->currentPlan()?->slug === 'vip';

        DB::transaction(function () use ($activeSubscription, $deactivateShop) {
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // VIP deactivate_shop option: auction seller verification remains approved
        });

        return redirect()->route('membership.manage')
            ->with('success', 'Your membership has been cancelled.');
    }

    /**
     * Cancel pending subscription (before first payment)
     */
    public function cancelPending(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if ($subscription->payments()->where('status', 'paid')->exists()) {
            return redirect()->route('membership.manage')->with('error', 'Cannot cancel - payment already received.');
        }

        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return redirect()->route('membership.index')->with('info', 'Payment cancelled. You can select a plan when ready.');
    }

    /**
     * Upgrade to another plan
     */
    public function upgrade(Request $request, string $targetPlanSlug)
    {
        $plan = Plan::where('slug', $targetPlanSlug)->where('is_active', true)->firstOrFail();
        $user = Auth::user();
        $activeSubscription = $user->activeSubscription();

        if (! $activeSubscription) {
            return redirect()->route('membership.index')->with('error', 'Subscribe first.');
        }

        if ($activeSubscription->plan_id === $plan->id) {
            return redirect()->route('membership.manage')->with('info', 'You are already on this plan.');
        }

        $newSubscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => $plan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);

        $activeSubscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return redirect()->route('membership.payment', ['subscription' => $newSubscription->id])
            ->with('success', 'Upgraded to ' . $plan->name . '. Complete payment to activate.');
    }
}
