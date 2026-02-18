<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSellerApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
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
        
        // Admin has access to everything
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user is a seller
        if (!$user->seller) {
            return redirect()->route('seller.register')
                ->with('info', 'Please complete your seller registration first.');
        }

        // Check if seller is approved
        if ($user->seller->verification_status !== 'approved') {
            // Allow access to dashboard to show pending message
            if ($request->routeIs('seller.dashboard')) {
                return $next($request);
            }

            // Block all other seller features
            $restrictedFeatures = [
                'Seller Dashboard Tools',
                'Upload Products (Toyshop, Trading, Auction)',
                'Product Tracking Logistics',
                'Chat System',
                'Order Management',
                'Analytics & Reports',
                'Business Page',
            ];

            return redirect()->route('seller.dashboard')
                ->with('pending_approval', true)
                ->with('restricted_features', $restrictedFeatures)
                ->with('error', 'Your business account is pending admin approval. You cannot access this feature until your account is approved.');
        }

        return $next($request);
    }
}
