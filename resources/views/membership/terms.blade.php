@extends('layouts.toyshop')

@section('title', 'Terms & Conditions - ' . ($plan->name ?? '') . ' Membership - ToyHaven')

@push('styles')
<style>
    .terms-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 2px solid #e2e8f0;
        overflow: hidden;
    }
    .terms-header {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
        color: white;
        padding: 1.5rem 2rem;
    }
    .terms-header h1 { margin: 0; font-weight: 700; font-size: 1.5rem; }
    .terms-body {
        padding: 2rem;
        max-height: 60vh;
        overflow-y: auto;
    }
    .terms-body h3 { font-size: 1.1rem; margin-top: 1.25rem; margin-bottom: 0.5rem; color: #1e293b; }
    .terms-body p, .terms-body ul { color: #475569; font-size: 0.95rem; margin-bottom: 0.75rem; }
    .terms-body ul { padding-left: 1.5rem; }
    .terms-footer {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    .agree-check { display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 1rem; }
    .agree-check input[type="checkbox"] { margin-top: 0.25rem; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="terms-card">
                <div class="terms-header">
                    <h1><i class="bi bi-file-text me-2"></i>{{ $plan->name }} Membership – Terms & Conditions</h1>
                    <p class="mb-0 mt-1 opacity-90">Please read and accept before proceeding to payment.</p>
                </div>

                <div class="terms-body">
                    <p><strong>By subscribing to the {{ $plan->name }} plan</strong> (₱{{ number_format($plan->price, 0) }}/{{ $plan->interval === 'monthly' ? 'month' : 'year' }}), you agree to the following terms specific to this membership and to ToyHaven’s general platform terms.</p>

                    <h3>1. Membership and access</h3>
                    <ul>
                        <li>Your {{ $plan->name }} membership grants you the benefits and features listed on the plan page and in your account for the current billing period.</li>
                        <li>Access to auctions, bidding, and any plan-specific perks (e.g. early access, buyer’s premium rate, toyshop discount) is subject to your membership being active and in good standing.</li>
                    </ul>

                    <h3>2. Payment and billing</h3>
                    <ul>
                        <li>Subscription payments are processed via <strong>QR Ph</strong> (Philippine QR code payment). You will receive a receipt and confirmation after successful payment.</li>
                        <li>Fees are charged at the start of each billing period (monthly or yearly as selected). Refunds are subject to ToyHaven’s refund policy.</li>
                        <li>If payment fails or is not completed, we may suspend or cancel your membership until payment is resolved.</li>
                    </ul>

                    <h3>3. Cancellation and renewal</h3>
                    <ul>
                        <li>You may cancel your subscription at any time from your membership management page. Cancellation takes effect at the end of the current billing period.</li>
                        <li>Unless you cancel, your subscription will renew automatically at the then-current price for the same interval.</li>
                    </ul>

                    @if($plan->canCreateAuction())
                    <h3>4. Auction seller terms (VIP / seller plans)</h3>
                    <ul>
                        <li>If this plan allows you to create auction listings, you must also comply with ToyHaven’s auction seller rules and any separate auction seller verification process.</li>
                        <li>Listing limits (e.g. maximum active auctions) apply as stated for your plan. Exceeding them may result in restrictions or suspension.</li>
                        <li>You are responsible for the accuracy of listings, shipping obligations, and any applicable fees (e.g. buyer’s premium, listing fees) as per the plan and platform rules.</li>
                    </ul>
                    @endif

                    <h3>5. Acceptable use</h3>
                    <ul>
                        <li>You must use your membership and the platform in accordance with ToyHaven’s Acceptable Use Policy and community guidelines.</li>
                        <li>Abuse, fraud, or violation of these terms may result in suspension or termination of your membership without refund.</li>
                    </ul>

                    <h3>6. Changes to plans and terms</h3>
                    <ul>
                        <li>ToyHaven may update plan benefits, pricing, or these terms with reasonable notice. Continued use after the effective date constitutes acceptance unless you cancel.</li>
                    </ul>

                    <p class="mt-3 small text-muted">For full platform terms, privacy policy, and dispute resolution, please see ToyHaven’s main Terms of Service and Privacy Policy.</p>
                </div>

                <div class="terms-footer">
                    <form action="{{ route('membership.subscribe') }}" method="POST" id="terms-form">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                        <div class="agree-check">
                            <input type="checkbox" name="agree_terms" id="agree_terms" value="1" required
                                   class="form-check-input">
                            <label for="agree_terms" class="form-check-label">
                                I have read and agree to the Terms & Conditions for the <strong>{{ $plan->name }}</strong> membership and wish to proceed to payment.
                            </label>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('membership.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to plans
                            </a>
                            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                                <i class="bi bi-credit-card me-1"></i>Proceed to payment (₱{{ number_format($plan->price, 0) }})
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
