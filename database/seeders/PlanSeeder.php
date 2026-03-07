<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanTerms;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    protected function defaultTermsContent(): string
    {
        return '<div class="terms-content">
    <h5>Payment & Bid Winner Obligations</h5>
    <ul>
        <li>Winners must pay for won auction items within <strong>24 hours</strong> of auction end.</li>
        <li>If payment is not received within 24 hours, the 2nd highest bidder will be offered the item (Second Chance).</li>
    </ul>

    <h5 class="mt-4">Offense Policy for Missed Payments</h5>
    <ul>
        <li><strong>1st offense:</strong> 7 days suspended from auction (no access)</li>
        <li><strong>2nd offense:</strong> 60 days suspended from auction (no access)</li>
        <li><strong>3rd offense:</strong> Permanent BAN from auction including Individual and Business auction seller registration</li>
    </ul>

    <h5 class="mt-4">Escrow & Delivery</h5>
    <ul>
        <li>Payment is held by ToyHaven until product delivery is confirmed by the buyer.</li>
        <li>Upon confirmation of receipt, funds are released to the seller.</li>
        <li>If the buyer does not receive the item, they may report the seller for investigation.</li>
    </ul>

    <h5 class="mt-4">Anonymous Bidding</h5>
    <p>Bidder identities are displayed anonymously (e.g. Bidder_XXXX) to protect privacy.</p>

    <h5 class="mt-4">Seller Requirements</h5>
    <p>Individual sellers require: 2 Government-issued IDs, 1 Facial photo, Bank Statement. Business sellers require the same verification as Fully Verified Trusted Toyshop.</p>
</div>';
    }

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
                'can_register_individual_auction_seller' => false,
                'can_register_business_auction_seller' => false,
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
                'can_register_individual_auction_seller' => false,
                'can_register_business_auction_seller' => false,
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'price' => 499.00,
                'interval' => 'month',
                'description' => 'Full access including Individual and Business auction seller registration with seller admin.',
                'features' => ['Everything in Pro', 'Register as Individual Auction Seller', 'Register as Business Auction Seller', 'Seller admin for both types', 'Professional tools'],
                'sort_order' => 3,
                'is_active' => true,
                'can_register_individual_seller' => false,
                'can_register_business_seller' => false,
                'has_analytics_dashboard' => true,
                'can_register_individual_auction_seller' => true,
                'can_register_business_auction_seller' => true,
            ],
        ];

        $termsContent = $this->defaultTermsContent();

        foreach ($plans as $data) {
            $plan = Plan::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            if ($plan->planTerms()->count() === 0) {
                PlanTerms::create([
                    'plan_id' => $plan->id,
                    'content' => $termsContent,
                    'version' => '1.0',
                    'effective_at' => now(),
                ]);
            }
        }
    }
}
