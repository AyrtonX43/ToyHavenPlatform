@extends('layouts.toyshop')

@section('title', 'Terms & Conditions - ' . $plan->name . ' Membership - ToyHaven')

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
        padding: 2rem;
    }
    .terms-header h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem; }
    .terms-body {
        padding: 2rem;
        max-height: 400px;
        overflow-y: auto;
    }
    .terms-content {
        font-size: 0.95rem;
        line-height: 1.6;
        color: #334155;
    }
    .terms-content strong { color: #0f172a; }
    .terms-footer {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }
    .terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .terms-checkbox input { margin-top: 0.25rem; }
    .terms-checkbox label { cursor: pointer; font-weight: 600; color: #1e293b; }
</style>
@endpush

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="terms-card">
                <div class="terms-header">
                    <h1><i class="bi bi-file-text me-2"></i>{{ $plan->name }} Membership Terms</h1>
                    <p class="mb-0 opacity-90">Please read and accept before proceeding to payment.</p>
                </div>

                <div class="terms-body">
                    @if($plan->terms_and_conditions)
                        <div class="terms-content">
                            {!! \Illuminate\Support\Str::markdown($plan->terms_and_conditions) !!}
                        </div>
                    @else
                        <p class="text-muted">No specific terms for this plan. General platform terms apply.</p>
                    @endif
                </div>

                <div class="terms-footer">
                    <form action="{{ route('membership.agree-terms') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                        <div class="terms-checkbox">
                            <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" required
                                   class="form-check-input @error('terms_accepted') is-invalid @enderror">
                            <label for="terms_accepted">
                                I have read and agree to the {{ $plan->name }} membership terms and conditions.
                            </label>
                        </div>
                        @error('terms_accepted')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border: none;">
                                <i class="bi bi-check-circle me-2"></i>Accept & Continue to Payment
                            </button>
                            <a href="{{ route('membership.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Plans
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
