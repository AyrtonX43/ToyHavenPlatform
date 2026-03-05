@extends('layouts.admin-new')
@section('title', 'Listing #' . $listing->id . ' - Moderator')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">{{ $listing->title }}</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>User:</strong> {{ $listing->user->name }}</p>
            <p><strong>Status:</strong> {{ $listing->getStatusLabel() }}</p>
            @if($listing->status === 'pending_approval')
            <form action="{{ route('moderator.trade-listings.approve', $listing->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Approve</button>
            </form>
            <form action="{{ route('moderator.trade-listings.reject', $listing->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="text" name="rejection_reason" class="form-control d-inline-block w-auto" placeholder="Reason">
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
