@extends('layouts.toyshop')

@section('title', 'Report - ToyHaven')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Report {{ ucfirst($type) }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="reportable_type" value="{{ $type }}">
                        <input type="hidden" name="reportable_id" value="{{ $id }}">

                        <div class="mb-3">
                            <label class="form-label">Report Type <span class="text-danger">*</span></label>
                            <select name="report_type" class="form-select @error('report_type') is-invalid @enderror" required>
                                <option value="">Select Report Type</option>
                                @if($type === 'product')
                                    <option value="fake">Fake/Counterfeit Product</option>
                                    <option value="inappropriate">Inappropriate Content</option>
                                    <option value="wrong_category">Wrong Category</option>
                                    <option value="misleading">Misleading Description</option>
                                    <option value="price_manipulation">Price Manipulation</option>
                                @else
                                    <option value="scam">Scam/Fraud</option>
                                    <option value="poor_service">Poor Service</option>
                                    <option value="non_delivery">Non-Delivery</option>
                                    <option value="harassment">Harassment</option>
                                    <option value="policy_violation">Policy Violation</option>
                                @endif
                            </select>
                            @error('report_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}" required>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Evidence (Screenshots, etc.)</label>
                            <input type="file" name="evidence[]" class="form-control @error('evidence') is-invalid @enderror" multiple accept="image/*">
                            <small class="text-muted">You can upload up to 5 images (Max: 2MB each)</small>
                            @error('evidence')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('evidence.*')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Your report will be reviewed by our admin team. We take all reports seriously and will take appropriate action.
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Submit Report</button>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
