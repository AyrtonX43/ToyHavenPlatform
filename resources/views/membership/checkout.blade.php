@extends('layouts.toyshop')

@section('title', 'Terms & Conditions - ' . $plan->name)

@push('styles')
<style>
    .terms-hero {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.2);
    }
    .terms-content-box {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        background: #f8fafc;
    }
    .terms-content-box::-webkit-scrollbar { width: 8px; }
    .terms-content-box::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
    .terms-content-box::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
    .btn-proceed { padding: 0.85rem 2rem; font-weight: 600; border-radius: 12px; }
</style>
@endpush

@section('content')
<div class="terms-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 opacity-90">
                <li class="breadcrumb-item"><a href="{{ route('membership.index') }}" class="text-white-50">Plans</a></li>
                <li class="breadcrumb-item"><a href="{{ route('membership.index') }}" class="text-white-50">{{ $plan->name }}</a></li>
                <li class="breadcrumb-item active text-white">Terms & Conditions</li>
            </ol>
        </nav>
        <h2 class="mt-3 mb-1 fw-bold"><i class="bi bi-file-text me-2"></i>{{ $plan->name }} - Terms & Conditions</h2>
        <p class="mb-0 opacity-90">Please read and accept before proceeding to payment</p>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-body p-4">
                    <div class="plan-summary mb-4 p-3 rounded-3 bg-light">
                        <span class="text-muted small fw-semibold">Selected Plan</span>
                        <h5 class="mb-0 fw-bold">{{ $plan->name }} — ₱{{ number_format($plan->price, 0) }}/mo</h5>
                    </div>
                    <div class="terms-content-box mb-4">
                        @include('membership.terms-content')
                    </div>
                    <form action="{{ route('membership.accept-terms') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <div class="form-check mb-4 p-3 rounded-3 border bg-white">
                            <input class="form-check-input" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" required>
                            <label class="form-check-label fw-semibold ms-2" for="termsAccepted">
                                I have read and agree to the Terms & Conditions
                            </label>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('membership.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Plans
                            </a>
                            <button type="submit" class="btn btn-primary btn-proceed">
                                <i class="bi bi-arrow-right me-2"></i>Proceed to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
