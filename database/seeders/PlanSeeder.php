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
                'description' => 'Start your auction journey—browse live auctions, place bids, and discover collectibles. Ideal for casual buyers and newcomers.',
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
                'description' => 'Unlock your auction analytics dashboard. See spending trends, bid history, and performance reports with priority customer support.',
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
                'description' => 'The complete package—everything in Pro plus Individual or Business seller registration. List auctions, use professional tools, and sell with confidence.',
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
