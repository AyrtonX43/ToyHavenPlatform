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
     * Update plan price and description
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'name' => 'required|string|max:255',
        ]);

        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }
}
