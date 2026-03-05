@extends('layouts.admin-new')
@section('title', 'Listing #' . $listing->id . ' - Admin')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">{{ $listing->title }}</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>User:</strong> {{ $listing->user->name }}</p>
            <p><strong>Status:</strong> {{ $listing->getStatusLabel() }}</p>
            <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $listing->trade_type)) }}</p>
            @if($listing->status === 'pending_approval')
            <form action="{{ route('admin.trades.approve-listing', $listing->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Approve</button>
            </form>
            <form action="{{ route('admin.trades.reject-listing', $listing->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="text" name="rejection_reason" class="form-control d-inline-block w-auto" placeholder="Reason (optional)">
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
