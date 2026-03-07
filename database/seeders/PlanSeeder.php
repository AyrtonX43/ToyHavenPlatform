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
                'description' => 'Access live auctions and place bids on collectibles. Ideal for casual collectors who want to participate without seller commitments.',
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
                'description' => 'Get an analytics dashboard to track your bidding activity and spending. Perfect for serious collectors who want insights and priority support.',
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
                'description' => 'Full platform access: register as Individual or Business auction seller, use professional tools, and access advanced analytics. Best for resellers and store owners.',
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
