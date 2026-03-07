@extends('layouts.toyshop')

@section('title', 'Register Auction Seller - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auctions.seller-profile.index') }}">Become a Seller</a></li>
            <li class="breadcrumb-item active">{{ $type === 'business' ? 'Business' : 'Individual' }}</li>
        </ol>
    </nav>

    <h2 class="mb-4">{{ $type === 'business' ? 'Register Auction Business' : 'Register as Individual Seller' }}</h2>

    <form action="{{ route('auctions.seller-profile.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="seller_type" value="{{ $type }}">

        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">PayPal Email <span class="text-danger">*</span></label>
                    <input type="email" name="paypal_email" class="form-control @error('paypal_email') is-invalid @enderror" value="{{ old('paypal_email', auth()->user()->email) }}" required>
                    <small class="text-muted">Where you will receive auction payments</small>
                    @error('paypal_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if($type === 'business')
                    <div class="mb-3">
                        <label class="form-label">Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" class="form-control @error('business_name') is-invalid @enderror" value="{{ old('business_name') }}" required>
                        @error('business_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">BIR Certificate <span class="text-danger">*</span></label>
                        <input type="file" name="bir_certificate" class="form-control @error('bir_certificate') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('bir_certificate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Official Receipt Sample <span class="text-danger">*</span></label>
                        <input type="file" name="official_receipt_sample" class="form-control @error('official_receipt_sample') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('official_receipt_sample')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit Application</button>
        <a href="{{ route('auctions.seller-profile.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
@endsection
