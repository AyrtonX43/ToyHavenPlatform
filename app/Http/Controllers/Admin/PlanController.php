<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
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
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update plan price, description, features, and capabilities
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'features' => 'nullable|string|max:4000',
            'can_register_individual_seller' => 'boolean',
            'can_register_business_seller' => 'boolean',
            'has_analytics_dashboard' => 'boolean',
        ]);

        $features = $request->features
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : [];

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'features' => $features,
            'can_register_individual_seller' => $request->boolean('can_register_individual_seller'),
            'can_register_business_seller' => $request->boolean('can_register_business_seller'),
            'has_analytics_dashboard' => $request->boolean('has_analytics_dashboard'),
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }
}
