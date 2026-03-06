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

        return view('membership.payment', [
            'subscription' => $subscription,
        ]);
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
            return back()->with('error', 'QR Ph payment is not configured. Please contact support.');
        }

        $amount = (float) $subscription->plan->price;
        $metadata = ['subscription_id' => (string) $subscription->id];

        $intent = $this->payMongo->createPaymentIntent($amount, 'PHP', $metadata);

        if (! $intent) {
            return back()->with('error', 'Could not create payment. Please try again.');
        }

        $clientKey = $intent['attributes']['client_key'] ?? null;
        if (! $clientKey) {
            return back()->with('error', 'Payment initialization failed.');
        }

        $returnUrl = url('/membership/payment-return') . '?' . http_build_query([
            'subscription_id' => $subscription->id,
        ]);

        return view('membership.payment', [
            'subscription' => $subscription,
            'client_key' => $clientKey,
            'payment_intent_id' => $intent['id'],
            'amount' => $amount,
            'return_url' => $returnUrl,
        ]);
    }

    protected function processPayPal(Subscription $subscription)
    {
        return back()->with('info', 'PayPal Sandbox integration will be available in the next phase.');
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
