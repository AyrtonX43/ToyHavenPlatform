<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    /**
     * Display the membership plans / pricing page
     */
    public function index(Request $request)
    {
        $plans = Plan::active()->ordered()->get();

        $activeAuctionsCount = 0;
        if (class_exists(\App\Models\Auction::class)) {
            $activeAuctionsCount = \App\Models\Auction::live()->count();
        }

        return view('membership.index', [
            'plans' => $plans,
            'intent' => $request->get('intent', session('intent')),
            'activeAuctionsCount' => $activeAuctionsCount,
        ]);
    }
}
