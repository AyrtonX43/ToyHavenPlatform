<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    /**
     * Subscribe to a plan - initiates PayMongo subscription flow
     */
    public function subscribe(Request $request)
    {
        $plan = Plan::where('slug', $request->get('plan', 'basic'))->active()->firstOrFail();

        $user = Auth::user();

        // Create PayMongo customer if needed
        $customer = $this->payMongo->createCustomer($user);
        if (! $customer) {
            return redirect()->route('membership.index')->with('error', 'Could not create payment profile. Please try again.');
        }

        $customerId = $customer['id'] ?? $user->paymongo_customer_id;
        if (! $customerId) {
            return redirect()->route('membership.index')->with('error', 'Could not create payment profile.');
        }

        // Ensure plan has PayMongo plan ID (create in PayMongo if missing)
        if (! $plan->paymongo_plan_id) {
            $pmPlan = $this->payMongo->createPlan(
                (int) $plan->price,
                $plan->interval,
                $plan->name,
                $plan->description ?? $plan->name
            );
            if ($pmPlan && isset($pmPlan['id'])) {
                $plan->update(['paymongo_plan_id' => $pmPlan['id']]);
            }
        }

        if (! $plan->paymongo_plan_id) {
            return redirect()->route('membership.index')->with('error', 'Plan is not configured for payments. Please contact support.');
        }

        // Create subscription in PayMongo
        $pmSubscription = $this->payMongo->createSubscription($customerId, $plan->paymongo_plan_id);
        if (! $pmSubscription) {
            return redirect()->route('membership.index')->with('error', 'Could not create subscription. Please try again.');
        }

        $attrs = $pmSubscription['attributes'] ?? $pmSubscription;
        $pmSubId = $attrs['id'] ?? $pmSubscription['id'] ?? null;

        // Create local subscription record (status may be pending until first payment)
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'paymongo_subscription_id' => $pmSubId,
            'paymongo_customer_id' => $customerId,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        // PayMongo subscriptions require attaching a payment method and paying the first invoice
        // For now we'll redirect to a checkout flow. The webhook will update status.
        // If PayMongo returns a payment redirect URL, use it.
        $latestInvoice = $attrs['latest_invoice'] ?? null;
        $paymentIntentId = null;
        if ($latestInvoice && is_array($latestInvoice)) {
            $pi = $latestInvoice['payment_intent'] ?? null;
            $paymentIntentId = is_array($pi) ? ($pi['id'] ?? null) : $pi;
        }

        if ($paymentIntentId) {
            return redirect()->route('membership.payment', [
                'subscription' => $subscription->id,
                'payment_intent' => $paymentIntentId,
            ]);
        }

        // Still redirect to payment page to show order summary and benefits
        return redirect()->route('membership.payment', ['subscription' => $subscription->id])
            ->with('success', 'Subscription created. Complete your payment to activate.');
    }

    /**
     * Payment page for subscription (attach payment method)
     */
    public function payment(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        $paymentIntentId = $request->get('payment_intent');
        $publicKey = config('services.paymongo.public_key');
        $clientKey = null;

        if ($paymentIntentId) {
            $intent = $this->payMongo->getPaymentIntent($paymentIntentId);
            if ($intent) {
                $attrs = $intent['attributes'] ?? $intent;
                $clientKey = $attrs['client_key'] ?? null;
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
     * Handle return from PayMongo after subscription payment (redirect from GCash/3DS etc)
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

        $intent = $this->payMongo->getPaymentIntent($paymentIntentId);
        $attrs = $intent['attributes'] ?? $intent ?? [];
        $status = $attrs['status'] ?? null;

        if ($status === 'succeeded' || $status === 'processing') {
            return redirect()->route('membership.manage')
                ->with('success', 'Payment successful! Your membership is now active.');
        }

        if ($status === 'awaiting_payment_method') {
            return redirect()->route('membership.payment', [
                'subscription' => $subscription,
                'payment_intent' => $paymentIntentId,
            ])->with('error', 'Payment could not be completed. Please try again.');
        }

        return redirect()->route('membership.payment', [
            'subscription' => $subscription,
            'payment_intent' => $paymentIntentId,
        ])->with('error', 'Payment status could not be verified. Please try again.');
    }

    /**
     * Manage current subscription
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
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $subscription = Subscription::where('user_id', Auth::id())
            ->whereNotNull('paymongo_subscription_id')
            ->active()
            ->first();

        if (! $subscription) {
            return redirect()->route('membership.manage')->with('error', 'No active subscription found.');
        }

        $result = $this->payMongo->cancelSubscription($subscription->paymongo_subscription_id);

        if ($result) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return redirect()->route('membership.manage')->with('success', 'Subscription cancelled. You will retain access until the end of your billing period.');
        }

        return redirect()->route('membership.manage')->with('error', 'Could not cancel subscription. Please contact support.');
    }
}
