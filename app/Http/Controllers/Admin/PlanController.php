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
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update plan price, description, features, and capabilities
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'name' => 'required|string|max:255',
            'features' => 'nullable|string',
            'can_register_individual_seller' => 'nullable|boolean',
            'can_register_business_seller' => 'nullable|boolean',
            'has_analytics_dashboard' => 'nullable|boolean',
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

    /**
     * Show edit form for plan terms
     */
    public function editTerms(Plan $plan)
    {
        $latestTerms = $plan->latestTerms();

        return view('admin.plans.terms', [
            'plan' => $plan,
            'terms' => $latestTerms,
        ]);
    }

    /**
     * Update plan terms (creates new version)
     */
    public function updateTerms(Request $request, Plan $plan)
    {
        $request->validate([
            'content' => 'required|string|max:50000',
        ]);

        $latest = $plan->latestTerms();
        $version = '1.0';
        if ($latest) {
            $parts = explode('.', $latest->version);
            $version = $parts[0] . '.' . ((int) ($parts[1] ?? 0) + 1);
        }

        PlanTerms::create([
            'plan_id' => $plan->id,
            'content' => $request->content,
            'version' => $version,
            'effective_at' => now(),
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Terms updated successfully for ' . $plan->name . '.');
    }
}
