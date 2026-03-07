@extends('layouts.toyshop')

@section('title', 'Terms & Conditions - ' . $plan->name)

@push('styles')
<style>
    .checkout-hero {
        background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(2, 132, 199, 0.2);
    }
    .terms-scroll {
        max-height: 360px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem;
        background: #f8fafc;
    }
    .terms-scroll::-webkit-scrollbar { width: 8px; }
    .terms-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
    .terms-scroll::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
</style>
@endpush

@section('content')
<div class="checkout-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 opacity-90">
                <li class="breadcrumb-item"><a href="{{ route('membership.index') }}" class="text-white-50">Membership</a></li>
                <li class="breadcrumb-item"><a href="{{ route('membership.index') }}" class="text-white-50">Plans</a></li>
                <li class="breadcrumb-item active text-white">{{ $plan->name }} - Terms</li>
            </ol>
        </nav>
        <h2 class="mt-3 mb-0 fw-bold"><i class="bi bi-file-earmark-check me-2"></i>{{ $plan->name }} - Terms & Conditions</h2>
        <p class="mb-0 opacity-90">Review and accept to proceed to payment</p>
    </div>
</div>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                <div class="card-body p-4">
                    <div class="terms-scroll mb-4">
                        {!! $termsContent !!}
                    </div>
                    <form action="{{ route('membership.accept-terms') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <div class="form-check mb-4 p-3 rounded-3" style="background: #f1f5f9;">
                            <input class="form-check-input" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" required style="width: 1.25em; height: 1.25em;" aria-describedby="termsAcceptedHelp">
                            <label class="form-check-label fw-semibold ms-2" for="termsAccepted" id="termsAcceptedHelp">
                                I have read and agree to the Terms &amp; Conditions
                            </label>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('membership.index') }}" class="btn btn-outline-secondary rounded-3">
                                <i class="bi bi-arrow-left me-2"></i>Back to Plans
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 px-4">
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
