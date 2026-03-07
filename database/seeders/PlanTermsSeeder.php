<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanTerms;
use Illuminate\Database\Seeder;

class PlanTermsSeeder extends Seeder
{
    /**
     * Default terms content (shared across all plans initially).
     */
    protected function defaultTermsContent(): string
    {
        return <<<'TERMS'
Payment & Bid Winner Obligations
• Winners must pay for won auction items within 24 hours of auction end.
• If payment is not received within 24 hours, the 2nd highest bidder will be offered the item (Second Chance).

Offense Policy for Missed Payments
• 1st offense: 7 days suspended from auction (no access)
• 2nd offense: 60 days suspended from auction (no access)
• 3rd offense: Permanent BAN from auction including Individual and Business auction seller registration

Escrow & Delivery
• Payment is held by ToyHaven until product delivery is confirmed by the buyer.
• Upon confirmation of receipt, funds are released to the seller.
• If the buyer does not receive the item, they may report the seller for investigation.

Anonymous Bidding
Bidder identities are displayed anonymously (e.g. Bidder_XXXX) to protect privacy.

Seller Requirements
Individual sellers require: 2 Government-issued IDs, 1 Facial photo, Bank Statement. Business sellers require the same verification as Fully Verified Trusted Toyshop.
TERMS;
    }

    public function run(): void
    {
        $plans = Plan::all();
        $content = $this->defaultTermsContent();

        foreach ($plans as $plan) {
            if ($plan->planTerms()->exists()) {
                continue;
            }

            PlanTerms::create([
                'plan_id' => $plan->id,
                'content' => $content,
                'version' => '1.0',
                'effective_at' => now(),
            ]);
        }
    }
}
