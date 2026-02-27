<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    /**
     * Subscribe to a plan — creates a local subscription (pending) and a PayMongo payment intent.
     */
    public function subscribe(Request $request)
    {
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
            return redirect()->route('membership.manage')->with('success', 'Your subscription is already active.');
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

            SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'amount' => data_get($intent, 'attributes.amount', 0) / 100,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return redirect()->route('membership.manage')
                ->with('success', 'Payment successful! Your membership is now active.');
        }

        return redirect()->route('membership.payment', ['subscription' => $subscription->id])
            ->with('error', 'Payment was not completed. Please try again.');
    }

    /**
     * Manage current subscription.
     */
    public function manage()
    {
        $user = Auth::user();
        $subscription = $user->subscriptions()->active()->first() ?? $user->subscriptions()->latest()->first();

        return view('membership.manage', [
            'subscription' => $subscription,
            'plans' => Plan::active()->ordered()->get(),
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

        $paymentType = $request->input('payment_type', 'card');

        $request->validate([
            'payment_type' => 'in:card,qrph',
            'card_number' => 'required_if:payment_type,card|nullable|string',
            'exp_month' => 'required_if:payment_type,card|nullable|integer|between:1,12',
            'exp_year' => 'required_if:payment_type,card|nullable|integer',
            'cvc' => 'required_if:payment_type,card|nullable|string|min:3|max:4',
        ]);

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
            $pmId = $this->payMongo->createCardPaymentMethod(
                $request->input('card_number'),
                (int) $request->input('exp_month'),
                (int) $request->input('exp_year'),
                $request->input('cvc'),
                $user->name ?? 'Customer',
                $user->email ?? ''
            );
            if (! $pmId) {
                return response()->json([
                    'error' => 'Failed to create card payment method. Please check your card details and try again.',
                ], 500);
            }
            $paymentMethodId = $pmId;
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

            SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'amount' => data_get($intent, 'attributes.amount', 0) / 100,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
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
