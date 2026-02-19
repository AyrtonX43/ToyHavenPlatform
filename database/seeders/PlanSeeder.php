<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'price' => 99,
                'interval' => 'monthly',
                'interval_count' => 1,
                'description' => 'Essential auction access for casual collectors.',
                'benefits' => [
                    'early_access_hours' => 0,
                    'buyers_premium_rate' => 5,
                    'toyshop_discount' => 0,
                    'free_shipping_min' => null,
                    'members_only_auctions' => false,
                    'priority_support' => false,
                    'badge_label' => 'Basic',
                ],
                'features' => [
                    'Access to auctions',
                    'Bid on auctions',
                    'Basic member badge',
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 249,
                'interval' => 'monthly',
                'interval_count' => 1,
                'description' => 'More perks for serious collectors.',
                'benefits' => [
                    'early_access_hours' => 24,
                    'buyers_premium_rate' => 2,
                    'toyshop_discount' => 5,
                    'free_shipping_min' => 500,
                    'members_only_auctions' => true,
                    'priority_support' => true,
                    'badge_label' => 'Pro',
                ],
                'features' => [
                    'Everything in Basic',
                    '24 hours early access to new auctions',
                    '2% buyer\'s premium',
                    'Members-only auctions',
                    '5% toyshop discount',
                    'Free shipping on orders ₱500+',
                    'Priority support',
                    'Pro badge',
                ],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'price' => 499,
                'interval' => 'monthly',
                'interval_count' => 1,
                'description' => 'Premium benefits for power collectors.',
                'benefits' => [
                    'early_access_hours' => 72,
                    'buyers_premium_rate' => 0,
                    'toyshop_discount' => 10,
                    'free_shipping_min' => 300,
                    'members_only_auctions' => true,
                    'priority_support' => true,
                    'badge_label' => 'VIP',
                ],
                'features' => [
                    'Everything in Pro',
                    '72 hours early access to new auctions',
                    'No buyer\'s premium',
                    '10% toyshop discount',
                    'Free shipping on orders ₱300+',
                    'VIP badge',
                ],
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
