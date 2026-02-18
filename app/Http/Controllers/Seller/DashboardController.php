<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:seller,admin');
    }

    public function index()
    {
        $seller = Auth::user()->seller;

        if (!$seller) {
            return redirect()->route('seller.register')->with('info', 'Please complete your seller registration first.');
        }

        // Statistics
        $stats = [
            'total_products' => Product::where('seller_id', $seller->id)->count(),
            'active_products' => Product::where('seller_id', $seller->id)->where('status', 'active')->count(),
            'pending_products' => Product::where('seller_id', $seller->id)->where('status', 'pending')->count(),
            'pending_orders' => Order::where('seller_id', $seller->id)->whereIn('status', ['pending', 'processing'])->count(),
            'completed_orders' => Order::where('seller_id', $seller->id)->where('status', 'delivered')->count(),
            'total_sales' => Order::where('seller_id', $seller->id)->where('payment_status', 'paid')->sum('total_amount'),
            'total_orders' => Order::where('seller_id', $seller->id)->count(),
            'today_orders' => Order::where('seller_id', $seller->id)->whereDate('created_at', today())->count(),
            'today_sales' => Order::where('seller_id', $seller->id)->where('payment_status', 'paid')->whereDate('created_at', today())->sum('total_amount'),
            'month_sales' => Order::where('seller_id', $seller->id)->where('payment_status', 'paid')->whereMonth('created_at', now()->month)->sum('total_amount'),
        ];

        // Recent orders
        $recentOrders = Order::with(['user', 'items'])
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('seller_id', $seller->id)
            ->where('stock_quantity', '<=', 10)
            ->where('status', 'active')
            ->limit(5)
            ->get();

        return view('seller.dashboard.index', compact('seller', 'stats', 'recentOrders', 'lowStockProducts'));
    }
}
