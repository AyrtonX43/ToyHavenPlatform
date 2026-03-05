@extends('layouts.toyshop')
@section('title', 'Offer - ToyHaven Trade')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Offer Details</h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Listing:</strong> {{ $offer->tradeListing->title }}</p>
            <p><strong>Status:</strong> {{ ucfirst($offer->status) }}</p>
            @if($offer->cash_amount)<p><strong>Cash:</strong> ₱{{ number_format($offer->cash_amount, 0) }}</p>@endif
            @if($offer->message)<p><strong>Message:</strong> {{ $offer->message }}</p>@endif
            @if($offer->tradeListing->user_id === auth()->id() && $offer->status === 'pending')
            <form action="{{ route('trading.offers.accept', $offer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">Accept</button>
            </form>
            <form action="{{ route('trading.offers.reject', $offer->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Reject</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
