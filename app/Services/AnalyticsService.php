<?php

namespace App\Services;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get user growth data for a period
     */
    public function getUserGrowth($days = 30)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'count' => User::whereDate('created_at', $date)->count(),
                'cumulative' => User::where('created_at', '<=', $date->endOfDay())->count(),
            ];
        }
        return $data;
    }

    /**
     * Get seller growth data for a period
     */
    public function getSellerGrowth($days = 30)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'count' => Seller::whereDate('created_at', $date)->count(),
                'cumulative' => Seller::where('created_at', '<=', $date->endOfDay())->count(),
            ];
        }
        return $data;
    }

    /**
     * Get product growth data for a period
     */
    public function getProductGrowth($days = 30)
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'count' => Product::whereDate('created_at', $date)->count(),
                'cumulative' => Product::where('created_at', '<=', $date->endOfDay())->count(),
            ];
        }
        return $data;
    }

    /**
     * Get revenue data for a period
     */
    public function getRevenueData($days = 30, $groupBy = 'day')
    {
        $data = [];
        $startDate = now()->subDays($days - 1)->startOfDay();
        
        if ($groupBy === 'day') {
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $data[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('M d'),
                    'revenue' => Order::where('payment_status', 'paid')
                        ->whereDate('created_at', $date)
                        ->sum('total_amount'),
                    'orders' => Order::whereDate('created_at', $date)->count(),
                ];
            }
        } elseif ($groupBy === 'week') {
            $weeks = ceil($days / 7);
            for ($i = $weeks - 1; $i >= 0; $i--) {
                $weekStart = now()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                $data[] = [
                    'date' => $weekStart->format('Y-m-d'),
                    'label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                    'revenue' => Order::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->sum('total_amount'),
                    'orders' => Order::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                ];
            }
        } elseif ($groupBy === 'month') {
            $months = ceil($days / 30);
            for ($i = $months - 1; $i >= 0; $i--) {
                $monthStart = now()->subMonths($i)->startOfMonth();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $data[] = [
                    'date' => $monthStart->format('Y-m-d'),
                    'label' => $monthStart->format('M Y'),
                    'revenue' => Order::where('payment_status', 'paid')
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->sum('total_amount'),
                    'orders' => Order::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                ];
            }
        }
        
        return $data;
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 10, $days = 30)
    {
        return Product::with('seller', 'category')
            ->whereHas('orderItems', function($query) use ($days) {
                $query->whereHas('order', function($orderQuery) use ($days) {
                    $orderQuery->where('payment_status', 'paid')
                               ->where('created_at', '>=', now()->subDays($days));
                });
            })
            ->withCount(['orderItems as total_sold' => function($query) use ($days) {
                $query->whereHas('order', function($orderQuery) use ($days) {
                    $orderQuery->where('payment_status', 'paid')
                               ->where('created_at', '>=', now()->subDays($days));
                })->selectRaw('sum(quantity)');
            }])
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top sellers by revenue
     */
    public function getTopSellersByRevenue($limit = 10, $days = 30)
    {
        return Seller::with('user')
            ->whereHas('orders', function($query) use ($days) {
                $query->where('payment_status', 'paid')
                      ->where('created_at', '>=', now()->subDays($days));
            })
            ->withSum(['orders as total_revenue' => function($query) use ($days) {
                $query->where('payment_status', 'paid')
                      ->where('created_at', '>=', now()->subDays($days));
            }], 'total_amount')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get category performance
     */
    public function getCategoryPerformance($days = 30)
    {
        return \App\Models\Category::withCount(['products as total_products'])
            ->withSum(['products as total_revenue' => function($query) use ($days) {
                $query->whereHas('orderItems', function($itemQuery) use ($days) {
                    $itemQuery->whereHas('order', function($orderQuery) use ($days) {
                        $orderQuery->where('payment_status', 'paid')
                                   ->where('created_at', '>=', now()->subDays($days));
                    });
                });
            }], 'price')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Get active users count
     */
    public function getActiveUsers($days = 30)
    {
        return User::whereHas('orders', function($query) use ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        })->count();
    }
}
