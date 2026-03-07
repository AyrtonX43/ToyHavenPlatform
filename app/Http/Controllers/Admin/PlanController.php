<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanTerms;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display all plans
     */
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->orderBy('price')->get();

        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show edit form for a plan
     */
    public function edit(Plan $plan)
    {
        $plan->load('latestTerms');

        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update plan price, description, features, terms, and capabilities
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:500',
            'terms_content' => 'nullable|string|max:50000',
        ]);

        $features = $request->features ? array_values(array_filter($request->features)) : [];

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'features' => $features,
            'can_register_individual_seller' => $request->boolean('can_register_individual_seller'),
            'can_register_business_seller' => $request->boolean('can_register_business_seller'),
            'has_analytics_dashboard' => $request->boolean('has_analytics_dashboard'),
            'can_register_individual_auction_seller' => $request->boolean('can_register_individual_auction_seller'),
            'can_register_business_auction_seller' => $request->boolean('can_register_business_auction_seller'),
        ]);

        if ($request->filled('terms_content')) {
            PlanTerms::create([
                'plan_id' => $plan->id,
                'content' => $request->terms_content,
                'version' => '1.' . ($plan->planTerms()->count() + 1),
                'effective_at' => now(),
            ]);
        }

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }
}
