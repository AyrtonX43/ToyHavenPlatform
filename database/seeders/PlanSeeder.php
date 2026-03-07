<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 99.00,
                'interval' => 'month',
                'description' => 'Access to view and bid on auctions. Perfect for casual collectors.',
                'features' => ['View live auctions', 'Place bids', 'Basic support'],
                'sort_order' => 1,
                'is_active' => true,
                'can_register_individual_seller' => false,
                'can_register_business_seller' => false,
                'has_analytics_dashboard' => false,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 249.00,
                'interval' => 'month',
                'description' => 'Analytics dashboard to track your auction spending and activity.',
                'features' => ['Everything in Basic', 'Analytics dashboard', 'Spending reports', 'Priority support'],
                'sort_order' => 2,
                'is_active' => true,
                'can_register_individual_seller' => false,
                'can_register_business_seller' => false,
                'has_analytics_dashboard' => true,
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'price' => 499.00,
                'interval' => 'month',
                'description' => 'Full access including Individual and Business auction seller registration.',
                'features' => ['Everything in Pro', 'Register as Individual seller', 'Register as Business seller', 'Seller analytics', 'Professional tools'],
                'sort_order' => 3,
                'is_active' => true,
                'can_register_individual_seller' => true,
                'can_register_business_seller' => true,
                'has_analytics_dashboard' => true,
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
