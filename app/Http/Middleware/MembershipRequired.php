<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MembershipRequired
{
    /**
     * Requires user to have an active membership (subscription).
     */
    public function handle(Request $request, Closure $next, ?string $intent = null): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->isAdmin()) {
            return $next($request);
        }

        if (! $request->user()->hasActiveMembership()) {
            $route = $intent === 'auction' ? 'auctions.index' : 'membership.index';

            return redirect()
                ->route($route, array_filter(['intent' => $intent]))
                ->with('info', 'Join our membership to access auctions and place bids.');
        }

        return $next($request);
    }
}
