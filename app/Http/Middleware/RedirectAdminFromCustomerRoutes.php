<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminFromCustomerRoutes
{
    /**
     * Handle an incoming request.
     * Redirect admins away from customer-specific routes to admin dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            // List of customer routes that admins should not access
            $customerRoutes = [
                'dashboard',
                'category-preferences.show',
                'category-preferences.store',
                'category-preferences.skip',
                'suggested-products',
                'cart.index',
                'cart.add',
                'cart.update',
                'cart.remove',
                'wishlist.index',
                'wishlist.add',
                'wishlist.remove',
                'wishlist.toggle',
                'checkout.index',
                'checkout.process',
                'checkout.payment',
                'orders.index',
                'orders.show',
                'orders.tracking',
                'reviews.product.store',
                'reviews.seller.store',
                'reports.create',
                'reports.store',
            ];

            // Check if current route is a customer route
            $routeName = $request->route()?->getName();
            if ($routeName && in_array($routeName, $customerRoutes)) {
                return redirect()->route('admin.dashboard')
                    ->with('info', 'Admins cannot access customer features. Please use the Admin Panel.');
            }

            // Also check URL patterns for customer routes
            $path = $request->path();
            $customerPathPatterns = [
                'cart',
                'wishlist',
                'checkout',
                'orders',
                'suggested-products',
                'welcome/categories',
            ];

            foreach ($customerPathPatterns as $pattern) {
                if (str_starts_with($path, $pattern)) {
                    return redirect()->route('admin.dashboard')
                        ->with('info', 'Admins cannot access customer features. Please use the Admin Panel.');
                }
            }
        }

        return $next($request);
    }
}
