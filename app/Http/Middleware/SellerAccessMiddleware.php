<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerAccessMiddleware
{
    /**
     * Allow: admin, seller, or user with seller moderator assignment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->canAccessSellerDashboard()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        abort(403, 'You need to be a seller or business moderator to access this area.');
    }
}
