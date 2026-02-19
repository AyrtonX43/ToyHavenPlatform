<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipRequired
{
    /**
     * Handle an incoming request.
     * Requires user to have an active membership (subscription).
     * Admins bypass this check.
     */
    public function handle(Request $request, Closure $next, string $intent = 'auction'): Response
    {
        if (! auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')->with('intent', $intent);
        }

        $user = auth()->user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasActiveMembership()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Membership required to access this feature.'], 403);
            }

            return redirect()->route('membership.index')->with('intent', $intent)->with('info', 'Join our membership to access auctions and place bids.');
        }

        return $next($request);
    }
}
