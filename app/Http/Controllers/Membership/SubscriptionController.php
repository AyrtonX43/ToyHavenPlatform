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
            $existing = $this->payMongo->getPaymentIntent($paymentIntentId);
            $existingStatus = $existing['attributes']['status'] ?? null;
            $clientKey = $existing['attributes']['client_key'] ?? null;

            if (in_array($existingStatus, ['succeeded', 'processing'])) {
                return redirect()->route('membership.payment-return', [
                    'subscription_id' => $subscription->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
            }

            if (! in_array($existingStatus, ['awaiting_payment_method'])) {
                $needsNewIntent = true;
            }
        }

        if ((! $paymentIntentId || $needsNewIntent) && $subscription->status === 'pending') {
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
     * Server-side: attach payment method to intent via PayMongo secret key.
     * Returns JSON with redirect_url for 3DS/e-wallet auth.
     */
    public function processPayment(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($subscription->status !== 'pending') {
            return response()->json(['error' => 'Subscription is not in pending state.'], 400);
        }

        $paymentIntentId = $subscription->paymongo_payment_intent_id;
        if (! $paymentIntentId) {
            return response()->json(['error' => 'No payment intent found for this subscription.'], 400);
        }

        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $returnUrl = url('/membership/payment-return') . '?' . http_build_query([
            'subscription_id' => $subscription->id,
            'payment_intent_id' => $paymentIntentId,
        ]);

        $result = $this->payMongo->attachPaymentMethod(
            $paymentIntentId,
            $request->payment_method_id,
            $returnUrl
        );

        if (! $result) {
            return response()->json(['error' => 'Failed to process payment. Please try again.'], 500);
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;

        Log::info('PayMongo processPayment: status and next_action', [
            'status' => $status,
            'next_action_type' => gettype($nextAction),
            'next_action_dump' => json_encode($nextAction),
        ]);

        if ($status === 'succeeded') {
            return response()->json([
                'status' => 'succeeded',
                'redirect_url' => $returnUrl,
            ]);
        }

        if ($status === 'awaiting_next_action') {
            $redirectUrl = $this->findAnyUrl($nextAction);
            if ($redirectUrl) {
                return response()->json([
                    'status' => 'awaiting_next_action',
                    'redirect_url' => $redirectUrl,
                ]);
            }

            Log::error('PayMongo: awaiting_next_action but no redirect URL found', [
                'next_action' => $nextAction,
                'full_attributes' => $result['attributes'] ?? [],
            ]);

            return response()->json([
                'error' => 'Payment requires bank verification but no redirect was provided. Please try again.',
                'debug_next_action' => $nextAction,
            ], 400);
        }

        if ($status === 'processing') {
            return response()->json([
                'status' => 'processing',
                'redirect_url' => $returnUrl,
            ]);
        }

        if ($status === 'awaiting_payment_method') {
            $errorMsg = $result['attributes']['last_payment_error']['message']
                ?? 'Payment failed. Please try again with a different payment method.';

            return response()->json(['error' => $errorMsg], 400);
        }

        return response()->json([
            'error' => "Payment returned unexpected status: {$status}",
        ], 400);
    }

    /**
     * Find any URL in a nested array/object structure.
     */
    private function findAnyUrl($data): ?string
    {
        if (is_string($data)) {
            if (str_starts_with($data, 'http://') || str_starts_with($data, 'https://')) {
                return $data;
            }

            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        // Check known PayMongo paths first
        $knownPaths = [
            ['redirect', 'url'],
            ['url'],
            ['redirect', 'return_url'],
        ];

        foreach ($knownPaths as $path) {
            $value = $data;
            foreach ($path as $key) {
                $value = $value[$key] ?? null;
                if ($value === null) {
                    break;
                }
            }
            if (is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
                return $value;
            }
        }

        // Brute-force: scan every value recursively
        foreach ($data as $value) {
            $found = $this->findAnyUrl($value);
            if ($found) {
                return $found;
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
