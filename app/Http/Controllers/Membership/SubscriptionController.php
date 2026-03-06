<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayMongoService;
use App\Services\SubscriptionReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo,
        protected SubscriptionReceiptService $receiptService
    ) {}

    /**
     * Subscribe to a plan — creates a local subscription (pending) and a PayMongo payment intent.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'agree_terms' => 'required|accepted',
        ]);

        $plan = Plan::where('slug', $request->get('plan', 'basic'))->active()->firstOrFail();
        $user = Auth::user();

        if ($user->hasActiveMembership()) {
            return redirect()->route('membership.manage')
                ->with('info', 'You already have an active subscription.');
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'current_period_start' => now(),
            'current_period_end' => $plan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);

        $intent = $this->payMongo->createPaymentIntent(
            (float) $plan->price,
            'PHP',
            [
                'subscription_id' => (string) $subscription->id,
                'plan_id' => (string) $plan->id,
                'user_id' => (string) $user->id,
            ]
        );

        if (! $intent) {
            $subscription->update(['status' => 'cancelled']);

            return redirect()->route('membership.index')
                ->with('error', 'Could not initialize payment. Please try again.');
        }

        $subscription->update([
            'paymongo_payment_intent_id' => $intent['id'],
        ]);

        return redirect()->route('membership.payment', [
            'subscription' => $subscription->id,
        ])->with('success', 'Subscription created. Complete your payment to activate.');
    }

    /**
     * Payment page for subscription.
     */
    public function payment(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $publicKey = config('services.paymongo.public_key');
        $paymentIntentId = $subscription->paymongo_payment_intent_id;
        $clientKey = null;
        $needsNewIntent = false;

        if ($paymentIntentId && $subscription->status === 'pending') {
            $fetched = $this->payMongo->getPaymentIntent($paymentIntentId);
            $intentStatus = $fetched['attributes']['status'] ?? 'unknown';

            if ($intentStatus === 'awaiting_payment_method') {
                $clientKey = $fetched['attributes']['client_key'] ?? null;
            } else {
                Log::info('PayMongo: Existing intent not reusable, creating fresh one', [
                    'old_intent_id' => $paymentIntentId,
                    'old_status' => $intentStatus,
                ]);
                $needsNewIntent = true;
            }
        } elseif (! $paymentIntentId && $subscription->status === 'pending') {
            $needsNewIntent = true;
        }

        if ($needsNewIntent) {
            $intent = $this->payMongo->createPaymentIntent(
                (float) $subscription->plan->price,
                'PHP',
                [
                    'subscription_id' => (string) $subscription->id,
                    'plan_id' => (string) $subscription->plan_id,
                    'user_id' => (string) $subscription->user_id,
                ]
            );

            if ($intent) {
                $paymentIntentId = $intent['id'];
                $clientKey = $intent['attributes']['client_key'] ?? null;
                $subscription->update(['paymongo_payment_intent_id' => $paymentIntentId]);
            }
        }

        return view('membership.payment', [
            'subscription' => $subscription,
            'paymentIntentId' => $paymentIntentId,
            'clientKey' => $clientKey,
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Handle return from PayMongo after payment redirect.
     */
    public function paymentReturn(Request $request)
    {
        $subscriptionId = $request->input('subscription_id');
        $paymentIntentId = $request->input('payment_intent_id');

        if (! $subscriptionId || ! $paymentIntentId) {
            return redirect()->route('membership.manage')->with('error', 'Invalid payment return.');
        }

        $subscription = Subscription::where('id', $subscriptionId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $subscription) {
            return redirect()->route('membership.manage')->with('error', 'Subscription not found.');
        }

        if ($subscription->status === 'active') {
            return redirect()->route('membership.payment-success', ['subscription' => $subscription->id])
                ->with('success', 'Your subscription is already active.');
        }

        $intent = $this->payMongo->getPaymentIntent($paymentIntentId);
        $status = data_get($intent, 'attributes.status');

        if ($status === 'succeeded') {
            $subscription->update([
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => $subscription->plan?->interval === 'yearly'
                    ? now()->addYear()
                    : now()->addMonth(),
            ]);

            $payment = SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'amount' => data_get($intent, 'attributes.amount', 0) / 100,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            try {
                $this->receiptService->generateReceipt($payment);
            } catch (\Throwable $e) {
                Log::warning('Subscription receipt generation failed', [
                    'subscription_payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect()->route('membership.payment-success', ['subscription' => $subscription->id])
                ->with('success', 'Payment successful! Your membership is now active.');
        }

        return redirect()->route('membership.payment', ['subscription' => $subscription->id])
            ->with('error', 'Payment was not completed. Please try again.');
    }

    /**
     * Notification / success page after successful membership payment.
     */
    public function paymentSuccess(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $latestPayment = $subscription->payments()->where('status', 'paid')->latest('paid_at')->first();

        return view('membership.payment-success', [
            'subscription' => $subscription,
            'latestPayment' => $latestPayment,
        ]);
    }

    /**
     * Process PayPal Demo payment (simulated - no real API).
     */
    public function paypalDemoConfirm(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if ($subscription->status !== 'pending') {
            return redirect()->route('membership.manage')
                ->with('error', 'Subscription is not in pending state.');
        }

        $subscription->update([
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => $subscription->plan?->interval === 'yearly'
                ? now()->addYear()
                : now()->addMonth(),
        ]);

        $payment = SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'amount' => (float) $subscription->plan->price,
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'paypal_demo',
        ]);

        try {
            $this->receiptService->generateReceipt($payment);
        } catch (\Throwable $e) {
            Log::warning('Subscription receipt generation failed (PayPal Demo)', [
                'subscription_payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('membership.payment-success', ['subscription' => $subscription->id])
            ->with('success', 'PayPal Demo payment successful! Your membership is now active.');
    }

    /**
     * Download receipt PDF for a subscription payment.
     */
    public function downloadReceipt(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $payment = $subscription->payments()->where('status', 'paid')->latest('paid_at')->first();

        if (! $payment) {
            abort(404, 'No paid payment found for this subscription.');
        }

        return $this->receiptService->downloadReceipt($payment);
    }

    /**
     * Manage current subscription.
     */
    public function manage()
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->with(['plan', 'scheduledPlan'])->active()->first()
            ?? $user->subscriptions()->with(['plan', 'scheduledPlan'])->latest()->first();

        if ($subscription) {
            $this->applyScheduledUpgradeIfDue($subscription);
            $subscription->refresh()->load(['plan', 'scheduledPlan']);
        }

        $analytics = $this->getMembershipAnalytics($user, $subscription);
        $upgradePlans = $subscription && $subscription->plan
            ? Plan::active()->ordered()->where('sort_order', '>', $subscription->plan->sort_order)->get()
            : collect();

        return view('membership.manage', [
            'subscription' => $subscription,
            'plans' => Plan::active()->ordered()->get(),
            'analytics' => $analytics,
            'upgradePlans' => $upgradePlans,
        ]);
    }

    /**
     * Analytics for current billing period: bids, wins, toyshop orders, discount saved.
     */
    protected function getMembershipAnalytics($user, $subscription): array
    {
        if (! $subscription || ! $subscription->current_period_start || ! $subscription->current_period_end) {
            return [
                'bids_count' => 0,
                'auctions_won_count' => 0,
                'toyshop_orders_count' => 0,
                'toyshop_discount_saved' => 0,
            ];
        }

        $start = $subscription->current_period_start;
        $end = $subscription->current_period_end;

        $bids_count = AuctionBid::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $auctions_won_count = AuctionPayment::where('winner_id', $user->id)
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->count();

        $toyshopOrders = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$start, $end]);
        $toyshop_orders_count = $toyshopOrders->count();
        $toyshop_discount_saved = (float) $toyshopOrders->sum('membership_discount_saved');

        return [
            'bids_count' => $bids_count,
            'auctions_won_count' => $auctions_won_count,
            'toyshop_orders_count' => $toyshop_orders_count,
            'toyshop_discount_saved' => $toyshop_discount_saved,
        ];
    }

    /**
     * Schedule upgrade at next period.
     */
    public function scheduleUpgrade(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $subscription = Subscription::where('user_id', Auth::id())->active()->first();
        if (! $subscription) {
            return redirect()->route('membership.manage')->with('error', 'No active subscription found.');
        }

        $newPlan = Plan::active()->findOrFail($request->plan_id);
        if ($newPlan->sort_order <= $subscription->plan->sort_order) {
            return redirect()->route('membership.manage')->with('error', 'Selected plan is not an upgrade.');
        }

        $subscription->update(['scheduled_plan_id' => $newPlan->id]);

        return redirect()->route('membership.manage')
            ->with('success', "You will be upgraded to {$newPlan->name} at the end of your current billing period.");
    }

    /**
     * Show upgrade payment page (prorated amount for immediate upgrade).
     */
    public function upgrade(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->active()->with('plan')->first();
        if (! $subscription) {
            return redirect()->route('membership.manage')->with('error', 'No active subscription found.');
        }

        $planId = $request->get('plan_id');
        if (! $planId) {
            return redirect()->route('membership.manage')->with('error', 'Please select a plan to upgrade to.');
        }

        $newPlan = Plan::active()->findOrFail($planId);
        if ($newPlan->sort_order <= $subscription->plan->sort_order) {
            return redirect()->route('membership.manage')->with('error', 'Selected plan is not an upgrade.');
        }

        $periodStart = $subscription->current_period_start;
        $periodEnd = $subscription->current_period_end;
        $daysTotal = $periodStart->diffInDays($periodEnd) ?: 1;
        $daysRemaining = now()->diffInDays($periodEnd, false);
        if ($daysRemaining < 0) {
            $daysRemaining = 0;
        }
        $currentPrice = (float) $subscription->plan->price;
        $newPrice = (float) $newPlan->price;
        $proratedCredit = $daysTotal > 0 ? $currentPrice * ($daysRemaining / $daysTotal) : 0;
        $amountDue = max(0, round($newPrice - $proratedCredit, 2));

        return view('membership.upgrade', [
            'subscription' => $subscription,
            'newPlan' => $newPlan,
            'amountDue' => $amountDue,
            'proratedCredit' => $proratedCredit,
        ]);
    }

    /**
     * Process immediate upgrade payment (prorated).
     */
    public function processUpgrade(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $user = Auth::user();
        $subscription = $user->subscriptions()->active()->with('plan')->first();
        if (! $subscription) {
            return redirect()->route('membership.manage')->with('error', 'No active subscription found.');
        }

        $newPlan = Plan::active()->findOrFail($request->plan_id);
        if ($newPlan->sort_order <= $subscription->plan->sort_order) {
            return redirect()->route('membership.manage')->with('error', 'Selected plan is not an upgrade.');
        }

        $periodStart = $subscription->current_period_start;
        $periodEnd = $subscription->current_period_end;
        $daysTotal = $periodStart->diffInDays($periodEnd) ?: 1;
        $daysRemaining = now()->diffInDays($periodEnd, false);
        if ($daysRemaining < 0) {
            $daysRemaining = 0;
        }
        $currentPrice = (float) $subscription->plan->price;
        $newPrice = (float) $newPlan->price;
        $proratedCredit = $daysTotal > 0 ? $currentPrice * ($daysRemaining / $daysTotal) : 0;
        $amountDue = max(0, round($newPrice - $proratedCredit, 2));

        if ($amountDue <= 0) {
            $subscription->update([
                'plan_id' => $newPlan->id,
                'scheduled_plan_id' => null,
                'current_period_start' => now(),
                'current_period_end' => $newPlan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            return redirect()->route('membership.manage')
                ->with('success', "Upgraded to {$newPlan->name}. No additional payment was required.");
        }

        $intent = $this->payMongo->createPaymentIntent(
            $amountDue,
            'PHP',
            [
                'subscription_id' => (string) $subscription->id,
                'plan_id' => (string) $newPlan->id,
                'user_id' => (string) $user->id,
                'type' => 'upgrade',
            ]
        );

        if (! $intent) {
            return redirect()->route('membership.manage')->with('error', 'Could not create payment. Please try again.');
        }

        session([
            'upgrade_subscription_id' => $subscription->id,
            'upgrade_plan_id' => $newPlan->id,
            'upgrade_intent_id' => $intent['id'],
            'upgrade_amount' => $amountDue,
        ]);

        return redirect()->route('membership.upgrade-payment', $subscription);
    }

    /**
     * Show upgrade payment page (QR Ph for prorated amount).
     */
    public function upgradePayment(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $intentId = session('upgrade_intent_id');
        $amount = session('upgrade_amount');
        $planId = session('upgrade_plan_id');

        if (! $intentId || ! $amount || ! $planId) {
            return redirect()->route('membership.manage')->with('error', 'Upgrade session expired. Please try again.');
        }

        $newPlan = Plan::find($planId);
        if (! $newPlan) {
            session()->forget(['upgrade_subscription_id', 'upgrade_plan_id', 'upgrade_intent_id', 'upgrade_amount']);

            return redirect()->route('membership.manage')->with('error', 'Invalid upgrade plan.');
        }

        $publicKey = config('services.paymongo.public_key');

        return view('membership.upgrade-payment', [
            'subscription' => $subscription,
            'newPlan' => $newPlan,
            'amount' => $amount,
            'intentId' => $intentId,
            'publicKey' => $publicKey,
        ]);
    }

    /**
     * Attach QR Ph to upgrade intent and return QR image.
     */
    public function processUpgradePayment(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $intentId = session('upgrade_intent_id');
        if (! $intentId) {
            return response()->json(['error' => 'Session expired.'], 400);
        }

        $user = Auth::user();
        $pmId = $this->payMongo->createQrphPaymentMethod($user->name, $user->email);
        if (! $pmId) {
            return response()->json(['error' => 'Failed to create payment method.'], 500);
        }

        $returnUrl = route('membership.manage');
        $result = $this->payMongo->attachPaymentMethod($intentId, $pmId, $returnUrl);
        if (! $result) {
            return response()->json(['error' => 'Failed to attach payment method.'], 500);
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;

        if ($status === 'succeeded') {
            $planId = session('upgrade_plan_id');
            $newPlan = Plan::find($planId);
            if ($newPlan) {
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'scheduled_plan_id' => null,
                    'current_period_start' => now(),
                    'current_period_end' => $newPlan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
                ]);
                session()->forget(['upgrade_subscription_id', 'upgrade_plan_id', 'upgrade_intent_id', 'upgrade_amount']);
            }

            return response()->json(['status' => 'succeeded', 'redirect_url' => route('membership.manage')]);
        }

        if ($status === 'awaiting_next_action' && $nextAction && ($nextAction['type'] ?? '') === 'consume_qr') {
            return response()->json([
                'status' => 'awaiting_next_action',
                'qr_image' => $nextAction['code']['image_url'] ?? null,
                'payment_intent_id' => $intentId,
            ]);
        }

        return response()->json(['status' => $status, 'redirect_url' => $returnUrl]);
    }

    /**
     * Poll upgrade payment intent and apply upgrade on success.
     */
    public function checkUpgradePayment(Request $request)
    {
        $subscriptionId = session('upgrade_subscription_id');
        $planId = session('upgrade_plan_id');
        $intentId = $request->input('payment_intent_id') ?? session('upgrade_intent_id');

        if (! $subscriptionId || ! $planId || ! $intentId) {
            return response()->json(['status' => 'invalid_session'], 400);
        }

        $subscription = Subscription::where('id', $subscriptionId)->where('user_id', Auth::id())->first();
        if (! $subscription) {
            return response()->json(['status' => 'unauthorized'], 403);
        }

        $intent = $this->payMongo->getPaymentIntent($intentId);
        $status = $intent['attributes']['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            $newPlan = Plan::find($planId);
            if ($newPlan) {
                $subscription->update([
                    'plan_id' => $newPlan->id,
                    'scheduled_plan_id' => null,
                    'current_period_start' => now(),
                    'current_period_end' => $newPlan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
                ]);
            }
            session()->forget(['upgrade_subscription_id', 'upgrade_plan_id', 'upgrade_intent_id', 'upgrade_amount']);

            return response()->json([
                'status' => 'succeeded',
                'redirect_url' => route('membership.manage'),
            ]);
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Apply scheduled upgrade at period end (called by cron or when viewing manage).
     */
    public function applyScheduledUpgradeIfDue(Subscription $subscription): void
    {
        if (! $subscription->scheduled_plan_id || ! $subscription->current_period_end || $subscription->current_period_end->isFuture()) {
            return;
        }

        $newPlan = Plan::find($subscription->scheduled_plan_id);
        if (! $newPlan || ! $newPlan->is_active) {
            $subscription->update(['scheduled_plan_id' => null]);

            return;
        }

        $subscription->update([
            'plan_id' => $newPlan->id,
            'scheduled_plan_id' => null,
            'current_period_start' => now(),
            'current_period_end' => $newPlan->interval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request)
    {
        $subscription = Subscription::where('user_id', Auth::id())
            ->active()
            ->first();

        if (! $subscription) {
            return redirect()->route('membership.manage')->with('error', 'No active subscription found.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('membership.manage')
            ->with('success', 'Subscription cancelled. You will retain access until the end of your billing period.');
    }

    /**
     * Server-side: create payment method and attach to intent via PayMongo secret key.
     * Returns JSON with status and redirect_url if 3DS/e-wallet auth is needed.
     */
    public function processPayment(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($subscription->status !== 'pending') {
            return response()->json(['error' => 'Subscription is not in pending state.'], 400);
        }

        $paymentType = $request->input('payment_type', 'qrph');

        $request->validate([
            'payment_type' => 'in:qrph',
        ], [
            'payment_type.in' => 'Membership payment is only available via QR Ph. Please use the QR code to pay.',
        ]);

        if ($paymentType !== 'qrph') {
            return response()->json([
                'error' => 'Membership payment is only available via QR Ph.',
            ], 400);
        }

        $paymentIntentId = $subscription->paymongo_payment_intent_id;
        $needsNewIntent = false;

        if (! $paymentIntentId) {
            $needsNewIntent = true;
        } else {
            $existingIntent = $this->payMongo->getPaymentIntent($paymentIntentId);
            $existingStatus = $existingIntent['attributes']['status'] ?? 'unknown';
            $allowedMethods = $existingIntent['attributes']['payment_method_allowed'] ?? [];

            // Create new intent if: status is not awaiting_payment_method, OR payment type not in allowed methods
            if ($existingStatus !== 'awaiting_payment_method' || ! in_array($paymentType, $allowedMethods)) {
                Log::info('Creating fresh payment intent', [
                    'reason' => $existingStatus !== 'awaiting_payment_method' ? 'status_mismatch' : 'method_not_allowed',
                    'old_status' => $existingStatus,
                    'old_allowed_methods' => $allowedMethods,
                    'requested_method' => $paymentType,
                ]);
                $needsNewIntent = true;
            }
        }

        if ($needsNewIntent) {
            $newIntent = $this->payMongo->createPaymentIntent(
                (float) $subscription->plan->price,
                'PHP',
                [
                    'subscription_id' => (string) $subscription->id,
                    'plan_id' => (string) $subscription->plan_id,
                    'user_id' => (string) $subscription->user_id,
                ]
            );

            if (! $newIntent) {
                return response()->json(['error' => 'Failed to create a new payment session. Please try again.'], 500);
            }

            $paymentIntentId = $newIntent['id'];
            $subscription->update(['paymongo_payment_intent_id' => $paymentIntentId]);
            Log::info('Fresh payment intent created', ['new_id' => $paymentIntentId]);
        }

        $user = Auth::user();

        if ($paymentType === 'qrph') {
            Log::info('Creating QRPh payment method', [
                'user_name' => $user->name,
                'user_email' => $user->email,
            ]);
            
            $pmId = $this->payMongo->createQrphPaymentMethod($user->name, $user->email);
            if (! $pmId) {
                Log::error('QRPh payment method creation returned null');
                return response()->json([
                    'error' => 'Failed to create QR Ph payment method. Please try again.',
                ], 500);
            }
            $paymentMethodId = $pmId;
        } else {
            return response()->json(['error' => 'Only QR Ph payment is accepted for membership.'], 400);
        }

        $returnUrl = url('/membership/payment-return') . '?' . http_build_query([
            'subscription_id' => $subscription->id,
            'payment_intent_id' => $paymentIntentId,
        ]);

        Log::info('Attaching payment method to intent', [
            'payment_intent_id' => $paymentIntentId,
            'payment_method_id' => $paymentMethodId,
            'return_url' => $returnUrl,
        ]);

        $result = $this->payMongo->attachPaymentMethod($paymentIntentId, $paymentMethodId, $returnUrl);

        if (! $result) {
            Log::error('Attach payment method returned null');
            return response()->json([
                'error' => 'Failed to attach payment method to intent. Please try again or contact support.',
                'debug' => 'Attach failed - check Laravel logs for PayMongo API response'
            ], 500);
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;

        Log::info('PayMongo: processPayment result', [
            'subscription_id' => $subscription->id,
            'payment_type' => $paymentType,
            'status' => $status,
            'next_action_raw' => json_encode($nextAction),
        ]);

        if ($status === 'succeeded') {
            return response()->json(['status' => 'succeeded', 'redirect_url' => $returnUrl]);
        }

        if ($status === 'awaiting_next_action' && $nextAction) {
            $nextActionType = $nextAction['type'] ?? null;

            if ($nextActionType === 'consume_qr') {
                return response()->json([
                    'status' => 'awaiting_next_action',
                    'qr_image' => $nextAction['code']['image_url'] ?? null,
                    'payment_intent_id' => $paymentIntentId,
                ]);
            }

            $redirectUrl = $this->extractRedirectUrl($nextAction);
            return response()->json([
                'status' => 'awaiting_next_action',
                'redirect_url' => $redirectUrl,
                'next_action' => $nextAction,
            ]);
        }

        if ($status === 'processing') {
            return response()->json(['status' => 'processing', 'redirect_url' => $returnUrl]);
        }

        if ($status === 'awaiting_payment_method') {
            $errorMsg = $result['attributes']['last_payment_error']['message']
                ?? 'Payment failed. Please try again with a different payment method.';
            return response()->json(['error' => $errorMsg], 400);
        }

        return response()->json(['error' => "Payment returned unexpected status: {$status}"], 400);
    }

    /**
     * Poll payment intent status (used by QRPh to detect when user has paid).
     */
    public function checkPaymentStatus(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $paymentIntentId = $subscription->paymongo_payment_intent_id;
        if (! $paymentIntentId) {
            return response()->json(['status' => 'unknown']);
        }

        $intent = $this->payMongo->getPaymentIntent($paymentIntentId);
        $status = $intent['attributes']['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            $subscription->update([
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => $subscription->plan?->interval === 'yearly'
                    ? now()->addYear()
                    : now()->addMonth(),
            ]);

            $payment = SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'amount' => data_get($intent, 'attributes.amount', 0) / 100,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            try {
                $this->receiptService->generateReceipt($payment);
            } catch (\Throwable $e) {
                Log::warning('Subscription receipt generation failed in checkPaymentStatus', [
                    'subscription_payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Recursively find a redirect URL from PayMongo's next_action object.
     */
    private function extractRedirectUrl($nextAction): ?string
    {
        if (is_string($nextAction)) {
            return filter_var($nextAction, FILTER_VALIDATE_URL) ? $nextAction : null;
        }

        if (! is_array($nextAction)) {
            return null;
        }

        if (! empty($nextAction['redirect']['url'])) {
            return $nextAction['redirect']['url'];
        }

        if (! empty($nextAction['url'])) {
            return $nextAction['url'];
        }

        foreach ($nextAction as $value) {
            if (is_array($value)) {
                $found = $this->extractRedirectUrl($value);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Cancel a pending (unpaid) subscription and redirect back to plan selection.
     */
    public function cancelPending(Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        if ($subscription->status !== 'pending') {
            return redirect()->route('membership.manage')
                ->with('error', 'Only pending subscriptions can be cancelled from the payment page.');
        }

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()->route('membership.index')
            ->with('info', 'Payment cancelled. You can choose a different plan below.');
    }
}
