<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index()
    {
        if (! Auth::user()->hasAuctionPermission('plans_manage')) {
            abort(403, 'You do not have permission to manage plans.');
        }
        $plans = Plan::ordered()->get();
        return view('admin.plans.index', compact('plans'))->with('planContext', 'moderator');
    }

    public function edit(Plan $plan)
    {
        if (! Auth::user()->hasAuctionPermission('plans_manage')) {
            abort(403, 'You do not have permission to manage plans.');
        }
        return view('admin.plans.edit', compact('plan'))->with('planContext', 'moderator');
    }

    public function update(Request $request, Plan $plan)
    {
        if (! Auth::user()->hasAuctionPermission('plans_manage')) {
            abort(403, 'You do not have permission to manage plans.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $plan->update($validated);

        return redirect()->route('moderator.plans.index')->with('success', 'Plan updated.');
    }
}
