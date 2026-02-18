<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Order;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->middleware('role:admin');
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $groupBy = $request->get('group_by', 'day'); // day, week, month
        
        // Platform Statistics
        $stats = [
            'total_users' => User::count(),
            'total_sellers' => Seller::count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'users_growth_30d' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'sellers_growth_30d' => Seller::where('created_at', '>=', now()->subDays(30))->count(),
            'products_growth_30d' => Product::where('created_at', '>=', now()->subDays(30))->count(),
            'revenue_30d' => Order::where('payment_status', 'paid')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('total_amount'),
            'active_users' => $this->analyticsService->getActiveUsers(30),
        ];
        
        // Growth Charts Data
        $userGrowth = $this->analyticsService->getUserGrowth((int)$period);
        $sellerGrowth = $this->analyticsService->getSellerGrowth((int)$period);
        $productGrowth = $this->analyticsService->getProductGrowth((int)$period);
        $revenueData = $this->analyticsService->getRevenueData((int)$period, $groupBy);
        
        // Top Performers
        $topProducts = $this->analyticsService->getTopSellingProducts(10, (int)$period);
        $topSellers = $this->analyticsService->getTopSellersByRevenue(10, (int)$period);
        $categoryPerformance = $this->analyticsService->getCategoryPerformance((int)$period);
        
        return view('admin.analytics.index', compact(
            'stats',
            'userGrowth',
            'sellerGrowth',
            'productGrowth',
            'revenueData',
            'topProducts',
            'topSellers',
            'categoryPerformance',
            'period',
            'groupBy'
        ));
    }
}
