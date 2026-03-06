@extends('layouts.toyshop')

@section('title', 'Auction Seller Registration')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Auction Seller Registration</h2>

    <form action="{{ route('auctions.verification.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-4">
            <label class="form-label fw-bold">Seller Type</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="seller_type" id="individual" value="individual" {{ old('seller_type', 'individual') === 'individual' ? 'checked' : '' }}>
                <label class="form-check-label" for="individual">Individual Seller</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="seller_type" id="business" value="business" {{ old('seller_type') === 'business' ? 'checked' : '' }}>
                <label class="form-check-label" for="business">Business Seller (requires Fully Verified Trusted Toyshop)</label>
            </div>
        </div>

        <div id="individual-form">
            <div class="card mb-4">
                <div class="card-header">Individual Seller - Documents Required</div>
                <div class="card-body">
                    <p class="text-muted small">2 Government-issued IDs, 1 Facial photo (selfie), Bank Statement</p>
                    <div class="mb-3">
                        <label class="form-label">Government ID 1</label>
                        <input type="file" name="gov_id_1" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('gov_id_1')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Government ID 2</label>
                        <input type="file" name="gov_id_2" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('gov_id_2')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Facial Photo (Selfie)</label>
                        <input type="file" name="facial" class="form-control" accept=".jpg,.jpeg,.png" required>
                        @error('facial')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Statement</label>
                        <input type="file" name="bank_statement" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        @error('bank_statement')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div id="business-form" style="display: none;">
            <div class="card mb-4">
                <div class="card-header">Business Seller</div>
                <div class="card-body">
                    @if($verifiedSellers->isNotEmpty())
                        <p class="text-muted small">Select your verified Toyshop to link as Business Auction Seller.</p>
                        <div class="mb-3">
                            <label class="form-label">Verified Toyshop</label>
                            <select name="seller_id" class="form-select">
                                @foreach($verifiedSellers as $s)
                                    <option value="{{ $s->id }}">{{ $s->business_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            You need a Fully Verified Trusted Toyshop to register as a Business Auction Seller.
                            <a href="{{ route('seller.shop-upgrade.index') }}">Upgrade your Toyshop</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('auctions.verification.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@push('scripts')
<script>
function toggleForms() {
    const isIndividual = document.querySelector('input[name="seller_type"]:checked')?.value === 'individual';
    document.getElementById('individual-form').style.display = isIndividual ? 'block' : 'none';
    document.getElementById('business-form').style.display = isIndividual ? 'none' : 'block';
    document.querySelectorAll('#individual-form input, #individual-form textarea').forEach(i => {
        i.required = isIndividual;
        i.disabled = !isIndividual;
    });
}
document.querySelectorAll('input[name="seller_type"]').forEach(r => {
    r.addEventListener('change', toggleForms);
});
toggleForms();
</script>
@endpush
@endsection
