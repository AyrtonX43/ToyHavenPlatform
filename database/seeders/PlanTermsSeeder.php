<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanTerms;
use Illuminate\Database\Seeder;

class PlanTermsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultContent = <<<'HTML'
<div class="terms-content">
    <section class="terms-section">
        <h5>1. Introduction and Acceptance</h5>
        <p>By subscribing to a ToyHaven Auctions membership plan, you agree to these Terms and Conditions. Please read them carefully before proceeding. Your payment constitutes acceptance of these terms.</p>
    </section>

    <section class="terms-section">
        <h5>2. Membership Plans and Pricing</h5>
        <p>ToyHaven offers tiered membership plans (Basic, Pro, VIP). Plan benefits, pricing, and features are subject to change. Any changes will be communicated in advance. Current plan details are displayed at the time of purchase.</p>
    </section>

    <section class="terms-section">
        <h5>3. Payment and Billing</h5>
        <p>Membership fees are billed in accordance with your selected plan (e.g., monthly). Payment is due at the start of each billing period. By subscribing, you authorize ToyHaven to charge your chosen payment method for recurring fees until you cancel.</p>
    </section>

    <section class="terms-section">
        <h5>4. Bid Winner Obligations</h5>
        <ul>
            <li>Winners must pay for won auction items within <strong>24 hours</strong> of auction end.</li>
            <li>If payment is not received within 24 hours, the second-highest bidder will be offered the item (Second Chance).</li>
        </ul>
    </section>

    <section class="terms-section">
        <h5>5. Offense Policy for Missed Payments</h5>
        <ul>
            <li><strong>1st offense:</strong> 7 days suspended from auction (no access).</li>
            <li><strong>2nd offense:</strong> 60 days suspended from auction (no access).</li>
            <li><strong>3rd offense:</strong> Permanent ban from auction, including Individual and Business auction seller registration.</li>
        </ul>
    </section>

    <section class="terms-section">
        <h5>6. Escrow and Delivery</h5>
        <ul>
            <li>Payment is held by ToyHaven until product delivery is confirmed by the buyer.</li>
            <li>Upon confirmation of receipt, funds are released to the seller.</li>
            <li>If the buyer does not receive the item, they may report the seller for investigation.</li>
        </ul>
    </section>

    <section class="terms-section">
        <h5>7. Anonymous Bidding</h5>
        <p>Bidder identities are displayed anonymously (e.g., Bidder_XXXX) to protect privacy during auctions.</p>
    </section>

    <section class="terms-section">
        <h5>8. Seller Requirements</h5>
        <p><strong>Individual sellers</strong> require: 2 government-issued IDs, 1 facial photo, and a bank statement.</p>
        <p><strong>Business sellers</strong> require the same verification as Fully Verified Trusted Toyshop.</p>
    </section>

    <section class="terms-section">
        <h5>9. Cancellation and Refunds</h5>
        <p>You may cancel your membership at any time. Cancellation takes effect at the end of the current billing period. Refunds are issued according to our refund policy applicable at the time of cancellation.</p>
    </section>

    <section class="terms-section">
        <h5>10. Dispute Resolution</h5>
        <p>Disputes between users or regarding transactions should be reported to ToyHaven support. We reserve the right to investigate and resolve disputes in accordance with our policies.</p>
    </section>

    <section class="terms-section">
        <h5>11. Limitation of Liability</h5>
        <p>ToyHaven and its affiliates are not liable for indirect, incidental, or consequential damages arising from your use of the membership or auction services, to the maximum extent permitted by law.</p>
    </section>

    <section class="terms-section">
        <h5>12. Changes to Terms</h5>
        <p>We may update these Terms and Conditions from time to time. Continued use of your membership after changes constitutes acceptance of the updated terms. Material changes will be communicated via email or in-app notification.</p>
    </section>
</div>
HTML;

        foreach (Plan::all() as $plan) {
            if ($plan->planTerms()->exists()) {
                continue;
            }

            PlanTerms::create([
                'plan_id' => $plan->id,
                'content' => $defaultContent,
                'version' => '1.0',
                'effective_at' => now(),
            ]);
        }
    }
}
