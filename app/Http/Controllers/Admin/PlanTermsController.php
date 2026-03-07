<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanTerms;
use Illuminate\Http\Request;

class PlanTermsController extends Controller
{
    /**
     * List terms for a plan
     */
    public function index(Plan $plan)
    {
        $plan->load('planTerms');
        $terms = $plan->planTerms()->orderByDesc('effective_at')->orderByDesc('created_at')->get();

        return view('admin.plan-terms.index', [
            'plan' => $plan,
            'terms' => $terms,
        ]);
    }

    /**
     * Show edit form for plan terms (create if none exist)
     */
    public function edit(Plan $plan)
    {
        $terms = $plan->latestTerms();

        return view('admin.plan-terms.edit', [
            'plan' => $plan,
            'terms' => $terms,
        ]);
    }

    /**
     * Create or update plan terms
     */
    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'content' => 'required|string|max:10000',
            'version' => 'nullable|string|max:50',
            'effective_at' => 'nullable|date',
        ]);

        $terms = $plan->latestTerms();

        if ($terms) {
            $terms->update([
                'content' => $request->content,
                'version' => $request->version ?: $terms->version,
                'effective_at' => $request->effective_at ? $request->date('effective_at') : $terms->effective_at,
            ]);
            $message = 'Terms updated successfully.';
        } else {
            PlanTerms::create([
                'plan_id' => $plan->id,
                'content' => $request->content,
                'version' => $request->version ?: '1.0',
                'effective_at' => $request->effective_at ? $request->date('effective_at') : now(),
            ]);
            $message = 'Terms created successfully.';
        }

        return redirect()->route('admin.plans.terms.index', $plan)
            ->with('success', $message);
    }
}
