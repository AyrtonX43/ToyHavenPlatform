<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModeratorRole
{
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        if (!$user->canManageOrders() && !$user->canManageDisputes()) {
            abort(403, 'You do not have permission to access this area.');
        }

        if ($permission && !$user->hasModeratorPermission($permission)) {
            abort(403, 'You do not have the required permission.');
        }

        return $next($request);
    }
}
