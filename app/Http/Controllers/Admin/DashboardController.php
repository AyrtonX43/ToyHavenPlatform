<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index()
    {
        // Platform Statistics
        $stats = [
            'total_users' => User::count(),
            'total_sellers' => Seller::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'pending_sellers' => Seller::where('verification_status', 'pending')->count(),
            'pending_products' => Product::where('status', 'pending')->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'active_products' => Product::where('status', 'active')->count(),
            'active_sellers' => Seller::where('is_active', true)->where('verification_status', 'approved')->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::where('payment_status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'month_revenue' => Order::where('payment_status', 'paid')->whereMonth('created_at', now()->month)->sum('total_amount'),
        ];

        // Growth Statistics (Last 30 days)
        $stats['users_growth_30d'] = User::where('created_at', '>=', now()->subDays(30))->count();
        $stats['sellers_growth_30d'] = Seller::where('created_at', '>=', now()->subDays(30))->count();
        $stats['products_growth_30d'] = Product::where('created_at', '>=', now()->subDays(30))->count();
        $stats['orders_growth_30d'] = Order::where('created_at', '>=', now()->subDays(30))->count();

        // Recent Activity
        $recentOrders = Order::with(['user', 'seller'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentSellers = Seller::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentProducts = Product::with(['seller', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentReports = Report::with(['reporter', 'reportable'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data - Last 7 days revenue
        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenueData[] = [
                'date' => $date->format('M d'),
                'revenue' => Order::where('payment_status', 'paid')
                    ->whereDate('created_at', $date)
                    ->sum('total_amount')
            ];
        }

        // Chart Data - Last 7 days orders
        $ordersData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $ordersData[] = [
                'date' => $date->format('M d'),
                'count' => Order::whereDate('created_at', $date)->count()
            ];
        }

        return view('admin.dashboard.index', compact(
            'stats', 
            'recentOrders', 
            'recentSellers', 
            'recentProducts', 
            'recentReports',
            'revenueData',
            'ordersData'
        ));
    }
}
