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
<article class="terms-content">
    <p class="terms-intro">
        These Terms and Conditions ("Terms") govern your use of the ToyHaven Auctions membership platform.
        By subscribing to a membership plan, you agree to be bound by these Terms. Please read them carefully.
    </p>

    <section class="terms-section">
        <h2 class="terms-h2">1. Introduction</h2>
        <p>
            ToyHaven Auctions ("Platform") provides a membership-based auction service for collectible toys and related items.
            These Terms apply to all members, whether bidding, selling, or using platform features. Continued use of the
            Platform constitutes acceptance of these Terms and any amendments.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">2. Eligibility</h2>
        <p>
            To use the auction features, you must:
        </p>
        <ul>
            <li>Be at least 18 years of age or the age of majority in your jurisdiction</li>
            <li>Maintain a valid, active membership plan</li>
            <li>Provide accurate registration and verification information when required</li>
            <li>Comply with all applicable laws and Platform policies</li>
        </ul>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">3. Payment and Bid Winner Obligations</h2>
        <p>Winning bidders ("Winners") have the following obligations:</p>
        <ul>
            <li>Winners must pay for won auction items within <strong>24 hours</strong> of auction end</li>
            <li>Payment must be completed through the Platform's designated payment methods</li>
            <li>If payment is not received within 24 hours, the seller may offer the item to the second-highest bidder ("Second Chance")</li>
            <li>Failure to pay may result in suspension or permanent ban per the Offense Policy below</li>
        </ul>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">4. Offense Policy for Missed Payments</h2>
        <p>The Platform enforces the following penalties for missed or late payments:</p>
        <ul>
            <li><strong>1st offense:</strong> 7 days suspended from auction (no access to bidding or selling)</li>
            <li><strong>2nd offense:</strong> 60 days suspended from auction (no access)</li>
            <li><strong>3rd offense:</strong> Permanent ban from auction, including Individual and Business auction seller registration</li>
        </ul>
        <p>
            Appeals may be submitted to support; ToyHaven reserves the right to uphold or waive penalties at its sole discretion.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">5. Escrow and Delivery</h2>
        <ul>
            <li>Payment is held in escrow by ToyHaven until product delivery is confirmed by the buyer</li>
            <li>Upon confirmation of receipt, funds are released to the seller</li>
            <li>If the buyer does not receive the item or receives a non-conforming item, they may report the seller for investigation</li>
            <li>Disputes are handled in accordance with the Dispute Resolution section below</li>
        </ul>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">6. Anonymous Bidding</h2>
        <p>
            Bidder identities are displayed anonymously (e.g., Bidder_XXXX) to protect privacy and reduce bid manipulation.
            Sellers and other bidders will not see your full identity until a transaction is completed.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">7. Seller Requirements</h2>
        <p>
            To register as an auction seller, you must meet verification requirements:
        </p>
        <ul>
            <li><strong>Individual sellers:</strong> 2 Government-issued IDs, 1 facial photo, and a valid bank statement</li>
            <li><strong>Business sellers:</strong> Same verification standards as Fully Verified Trusted Toyshop, including business registration and authorized representative identification</li>
        </ul>
        <p>
            ToyHaven reserves the right to reject or revoke seller status if verification is incomplete or fraudulent.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">8. Dispute Resolution</h2>
        <p>
            Disputes between buyers and sellers should be reported through the Platform. ToyHaven will investigate and may:
        </p>
        <ul>
            <li>Hold or release escrow funds</li>
            <li>Suspend or terminate accounts involved in fraudulent or abusive conduct</li>
            <li>Mediate between parties where appropriate</li>
        </ul>
        <p>
            ToyHaven's decisions on disputes are final. For legal matters, you agree to pursue resolution through the courts of the jurisdiction specified in the Governing Law section.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">9. Modifications</h2>
        <p>
            ToyHaven may modify these Terms at any time. Material changes will be communicated via email or in-app notice.
            Continued use of the Platform after changes constitutes acceptance of the updated Terms. If you do not agree,
            you may cancel your membership per the cancellation policy.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">10. Governing Law</h2>
        <p>
            These Terms are governed by the laws of the Republic of the Philippines. Any disputes arising from these Terms
            or your use of the Platform shall be subject to the exclusive jurisdiction of the courts of the Philippines.
        </p>
    </section>

    <section class="terms-section">
        <h2 class="terms-h2">11. Contact</h2>
        <p>
            For questions about these Terms or your membership, contact ToyHaven support through the Platform's
            help center or at the contact details provided in your account settings.
        </p>
    </section>
</article>
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
