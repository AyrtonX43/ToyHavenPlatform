<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVipForAuctionSeller
{
    /**
     * Requires user to have VIP membership (or a plan with auction seller capability).
     */
    public function handle(Request $request, Closure $next, ?string $typeParam = null): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        $plan = $request->user()->currentPlan();

        if (! $plan) {
            return redirect()
                ->route('membership.index')
                ->with('info', 'VIP membership is required to register as an auction seller.');
        }

        $type = $typeParam ?? $request->route('type', 'individual');

        $allowed = $type === 'business'
            ? ($plan->can_register_business_auction_seller ?? false)
            : ($plan->can_register_individual_auction_seller ?? false);

        if (! $allowed) {
            return redirect()
                ->route('membership.index')
                ->with('info', 'VIP membership is required to register as an auction seller. Please upgrade your plan.');
        }

        return $next($request);
    }
}
