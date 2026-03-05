<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TradeNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if (Auth::user()->isTradeSuspended()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your trade access has been suspended. Please contact support for more information.',
                ], 403);
            }

            return redirect()->route('trading.index')
                ->with('error', 'Your trade access has been suspended. Please contact support for more information.');
        }

        return $next($request);
    }
}
