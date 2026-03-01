@extends('layouts.admin-new')

@section('title', 'Edit Seller - ToyHaven')
@section('page-title', 'Edit Seller: ' . $seller->business_name)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Seller Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sellers.update', $seller->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Business Name <span class="text-danger">*</span></label>
                    <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $seller->business_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $seller->email) }}">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $seller->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $seller->postal_code) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $seller->address) }}">
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $seller->city) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Province</label>
                    <input type="text" name="province" class="form-control" value="{{ old('province', $seller->province) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $seller->description) }}</textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Seller</button>
            </div>
        </form>
    </div>
</div>
@endsection
