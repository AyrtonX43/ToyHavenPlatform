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
                'terms_and_conditions' => "**ToyHaven Auction Membership – Basic Plan Terms**

By subscribing to the Basic plan, you agree to:

1. **Eligibility**: You must be 18+ and able to form binding contracts. You provide accurate registration information.

2. **Membership Scope**: Basic membership grants access to browse and bid on ToyHaven auctions. A 5% buyer's premium applies to winning bids.

3. **Bidding Obligations**: All bids are binding. If you win, you must complete payment within the stated deadline. Failure may result in account restrictions.

4. **Fees & Billing**: You will be charged ₱99/month. Billing recurs automatically until you cancel. No refunds for partial periods.

5. **Cancellation**: You may cancel anytime from your membership management page. Access continues until the end of your billing period.

6. **Conduct**: You agree to comply with ToyHaven's community guidelines and prohibited items policy. Fraudulent or abusive behavior may result in immediate termination.

7. **Platform Terms**: Your use is also governed by our general Terms of Service and Privacy Policy.",
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
                'terms_and_conditions' => "**ToyHaven Auction Membership – Pro Plan Terms**

In addition to the general membership terms, Pro plan subscribers agree to:

1. **Early Access**: 24-hour early access to new auctions is a benefit, not a guarantee. ToyHaven may adjust timing for technical or operational reasons.

2. **Buyer's Premium**: A reduced 2% buyer's premium applies to winning bids (vs. 5% for Basic).

3. **Members-Only Auctions**: You may participate in members-only auctions. Access is subject to your subscription remaining active.

4. **Toyshop & Shipping Benefits**: 5% toyshop discount and free shipping on orders ₱500+ apply to the ToyShop marketplace only. Exclusions may apply.

5. **Priority Support**: Pro members receive priority handling for support requests. Response times are best-effort and not guaranteed.

6. **Fees & Billing**: You will be charged ₱249/month. Same billing, cancellation, and refund policies as Basic apply.

7. **Plan-Specific Rules**: ToyHaven may modify plan benefits with reasonable notice. Continued use after changes constitutes acceptance.",
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'VIP',
                'slug' => 'vip',
                'price' => 499,
                'interval' => 'monthly',
                'interval_count' => 1,
                'description' => 'Premium benefits for power collectors — including the ability to auction your own products.',
                'benefits' => [
                    'early_access_hours' => 72,
                    'buyers_premium_rate' => 0,
                    'toyshop_discount' => 10,
                    'free_shipping_min' => 300,
                    'members_only_auctions' => true,
                    'priority_support' => true,
                    'badge_label' => 'VIP',
                    'can_create_auction' => true,
                    'max_active_auctions' => 5,
                    'auction_listing_fee' => 0,
                ],
                'features' => [
                    'Everything in Pro',
                    '72 hours early access to new auctions',
                    'No buyer\'s premium',
                    'List & auction your own products (up to 5 active)',
                    'No auction listing fees',
                    '10% toyshop discount',
                    'Free shipping on orders ₱300+',
                    'VIP badge',
                ],
                'terms_and_conditions' => "**ToyHaven Auction Membership – VIP Plan Terms**

In addition to general and Pro terms, VIP (seller) plan subscribers agree to:

1. **Seller Verification**: You must complete and pass ToyHaven's auction seller verification before listing. Verification may be revoked if we detect policy violations.

2. **Listing Limits**: You may have up to 5 active auctions at once. \"Active\" includes draft, live, and pending-approval listings. Exceeding the limit may result in listing removal.

3. **Seller Obligations**: You accurately describe items, ship within the required timeframe after payment, and handle returns/refunds per ToyHaven policy. Misrepresentation may result in suspension or termination.

4. **Reserve Price**: If you set a reserve, you agree that bids below reserve do not create a binding sale. Once reserve is met, the auction is binding.

5. **Fees**: No buyer's premium on purchases; no auction listing fees for VIP. Platform fees for promotions or other services may apply.

6. **Fees & Billing**: You will be charged ₱499/month. Same billing and cancellation policies as other plans apply.

7. **Suspension**: ToyHaven may suspend your seller privileges or membership for policy violations, disputes, or operational reasons. You remain responsible for outstanding obligations.",
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
