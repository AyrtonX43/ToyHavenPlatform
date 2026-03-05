@extends('layouts.toyshop')
@section('title', 'My Trades - ToyHaven')
@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">My Trades</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if($trades->count() > 0)
    <div class="list-group">
        @foreach($trades as $trade)
        <a href="{{ route('trading.trades.show', $trade->id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <div>
                <strong>Trade #{{ $trade->id }}</strong>
                <span class="badge bg-{{ $trade->status === 'completed' ? 'success' : ($trade->status === 'cancelled' ? 'secondary' : 'primary') }} ms-2">{{ $trade->getStatusLabel() }}</span>
                <p class="mb-0 small text-muted">{{ $trade->tradeListing->title }}</p>
            </div>
            <i class="bi bi-chevron-right"></i>
        </a>
        @endforeach
    </div>
    {{ $trades->links() }}
    @else
    <p class="text-muted">No trades yet.</p>
    @endif
</div>
@endsection
