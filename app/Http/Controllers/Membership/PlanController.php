<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class PlanController extends Controller
{
    /**
     * Display the membership plans / pricing page
     */
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('membership.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Step 1: Terms & conditions (must accept before payment selection)
     */
    public function checkout(string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        return view('membership.checkout', ['plan' => $plan]);
    }

    /**
     * Step 2: Payment method selection (QRPH or PayPal)
     */
    public function paymentSelection(string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        if (request('payment_failed')) {
            return redirect()->route('membership.payment-selection', $planSlug)
                ->with('payment_failed', true);
        }

        $config = config('paypal', []);
        $paypalDemoMode = (bool) ($config['demo_mode'] ?? true);
        $mode = $config['mode'] ?? 'sandbox';
        $creds = $config[$mode] ?? $config['sandbox'] ?? [];
        $paypalClientId = $creds['client_id'] ?? '';

        return view('membership.payment-selection', [
            'plan' => $plan,
            'paypal_client_id' => $paypalClientId,
            'paypal_demo_mode' => $paypalDemoMode,
        ]);
    }

    /**
     * Display terms & conditions for a plan (read-only)
     */
    public function terms(string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        return view('membership.terms', ['plan' => $plan]);
    }
}
