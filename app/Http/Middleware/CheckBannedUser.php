<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip banned check for OAuth callback route to prevent interference with authentication
        if ($request->is('auth/google/callback')) {
            return $next($request);
        }
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is banned
            if ($user->is_banned ?? false) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your account has been banned. Please contact support for more information.'
                    ], 403);
                }
                
                return redirect()->route('login')
                    ->with('error', 'Your account has been banned. Please contact support for more information.');
            }
            
            // Check if user's seller account is suspended (only if user is a seller)
            if (($user->role === 'seller' || $user->seller) && !$user->relationLoaded('seller')) {
                $user->load('seller');
            }
            
            if ($user->seller && !$user->seller->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your business account has been suspended. Please contact support for more information.'
                    ], 403);
                }
                
                return redirect()->route('login')
                    ->with('error', 'Your business account has been suspended. Please contact support for more information.');
            }
        }
        
        return $next($request);
    }
}
