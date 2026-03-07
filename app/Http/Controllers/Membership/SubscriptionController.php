<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            if (empty($config['sandbox']['client_id']) || empty($config['sandbox']['client_secret'])) {
                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('error', 'PayPal is not configured. Please add PAYPAL_SANDBOX_CLIENT_ID and PAYPAL_SANDBOX_CLIENT_SECRET to .env');
            }

            $paypal = new PayPalService($config);
        } catch (\Throwable $e) {
            Log::error('PayPal init failed', ['error' => $e->getMessage()]);

            return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                ->with('error', 'PayPal could not be initialized. Please try again or use QR Ph.');
        }

        $returnUrl = route('membership.paypal.return');
        $cancelUrl = route('membership.paypal.cancel');

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => config('paypal.currency', 'PHP'),
                    'value' => number_format($subscription->plan->price, 2, '.', ''),
                ],
                'description' => $subscription->plan->name . ' Membership',
                'custom_id' => (string) $subscription->id,
            ]],
            'application_context' => [
                'return_url' => $returnUrl . '?subscription_id=' . $subscription->id,
                'cancel_url' => $cancelUrl . '?subscription_id=' . $subscription->id,
            ],
        ];

        try {
            $response = $paypal->createOrder($orderData);

            if (isset($response['error'])) {
                Log::error('PayPal create order error', ['response' => $response, 'subscription' => $subscription->id]);

                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('error', 'PayPal could not create the order. Please try again or use QR Ph.');
            }

            if (is_array($response) && isset($response['id']) && ($response['status'] ?? '') === 'CREATED') {
                foreach ($response['links'] ?? [] as $link) {
                    if (($link['rel'] ?? '') === 'approve') {
                        session(['paypal_subscription_id' => $subscription->id]);

                        return redirect()->away($link['href']);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('PayPal create order failed', [
                'error' => $e->getMessage(),
                'subscription' => $subscription->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return redirect()->route('membership.payment-selection', $subscription->plan->slug)
            ->with('error', 'PayPal payment could not be initiated. Please try again or choose QR Ph.');
    }

    /**
     * PayPal return (after user approves payment)
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
            $paypal = new PayPalService(config('paypal'));
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
                    ->with('success', 'Payment successful! Your receipt has been sent to your email.');
            }
        } catch (\Throwable $e) {
            Log::error('PayPal capture failed', ['error' => $e->getMessage(), 'token' => $token]);
        }

        return redirect()->route('membership.payment-selection', $subscription->plan->slug)
            ->with('error', 'PayPal payment could not be completed. Please try again.');
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
                return redirect()->route('membership.payment-selection', $subscription->plan->slug)
                    ->with('info', 'Payment was cancelled. You can try again or choose another payment method.');
            }
        }

        return redirect()->route('auctions.index')->with('info', 'Payment cancelled. You can select a plan when ready.');
    }

    /**
     * Payment return (after PayMongo redirect)
     */
    public function paymentReturn(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');

        if (! $subscriptionId) {
            return redirect()->route('membership.manage')->with('error', 'Invalid payment return.');
        }

        $subscription = Subscription::find($subscriptionId);

        if (! $subscription || $subscription->user_id !== Auth::id()) {
            return redirect()->route('membership.manage')->with('error', 'Subscription not found.');
        }

        return redirect()->route('membership.payment-success', ['subscription' => $subscription->id]);
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

        return redirect()->route('auctions.index')->with('info', 'Subscription cancelled. You can select a plan when ready.');
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
