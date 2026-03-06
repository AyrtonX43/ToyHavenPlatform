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
     * Display terms & conditions for a plan
     */
    public function terms(string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        return view('membership.terms', ['plan' => $plan]);
    }
}
