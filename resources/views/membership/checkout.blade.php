@extends('layouts.toyshop')

@section('title', 'Terms & Conditions - ' . $plan->name)

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Membership</a></li>
            <li class="breadcrumb-item active">{{ $plan->name }} - Terms</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="bi bi-file-text me-2"></i>{{ $plan->name }} - Terms & Conditions</h5>
                </div>
                <div class="card-body">
                    <div class="terms-content border rounded p-4 mb-4" style="max-height: 320px; overflow-y: auto;">
                        @include('membership.terms-content')
                    </div>
                    <form action="{{ route('membership.accept-terms') }}" method="POST">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="terms_accepted" id="termsAccepted" value="1" required>
                            <label class="form-check-label fw-semibold" for="termsAccepted">
                                I have read and agree to the Terms & Conditions
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-right me-2"></i>Proceed to Payment Selection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
