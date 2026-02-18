@extends('layouts.toyshop')

@section('title', 'Trade #' . $trade->id . ' - ToyHaven Trading')

@push('styles')
<style>
    .trade-show-header { background: white; border-radius: 14px; padding: 1.5rem 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
    .trade-card-block { background: white; border-radius: 14px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 1.5rem; }
    .trade-progress { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
    .trade-progress-bar { height: 100%; border-radius: 4px; background: #0891b2; }
    .item-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; }
</style>
@endpush

@section('content')
<div class="container my-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.index') }}">Trading</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.trades.index') }}">My Trades</a></li>
            <li class="breadcrumb-item active">Trade #{{ $trade->id }}</li>
        </ol>
    </nav>

    <div class="trade-show-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h1 class="h4 fw-bold mb-1">{{ $trade->tradeListing->title }}</h1>
            <p class="text-muted mb-2">With {{ $otherParty->name }} · Status: <strong>{{ $trade->getStatusLabel() }}</strong></p>
            <div class="trade-progress" style="max-width: 280px;">
                <div class="trade-progress-bar" style="width: {{ $trade->getProgressPercentage() }}%;"></div>
            </div>
        </div>
        @if($trade->conversation)
            <a href="{{ route('trading.conversations.show', $trade->conversation) }}" class="btn btn-primary">
                <i class="bi bi-chat-dots me-2"></i>Trade Chat
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="trade-card-block">
                <h5 class="fw-bold mb-3">Items in this trade</h5>
                <div class="row g-3">
                    @foreach($trade->initiatorItems as $item)
                        <div class="col-12 col-md-6">
                            <div class="d-flex gap-3 p-2 rounded-3 bg-light">
                                @if(!empty($item->product_images))
                                    <img src="{{ asset('storage/' . $item->product_images[0]) }}" alt="" class="item-thumb">
                                @else
                                    <div class="item-thumb bg-secondary d-flex align-items-center justify-content-center text-white"><i class="bi bi-box"></i></div>
                                @endif
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold">{{ $item->product_name }}</div>
                                    <small class="text-muted">Your item</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @foreach($trade->participantItems as $item)
                        <div class="col-12 col-md-6">
                            <div class="d-flex gap-3 p-2 rounded-3 bg-light">
                                @if(!empty($item->product_images))
                                    <img src="{{ asset('storage/' . $item->product_images[0]) }}" alt="" class="item-thumb">
                                @else
                                    <div class="item-thumb bg-secondary d-flex align-items-center justify-content-center text-white"><i class="bi bi-box"></i></div>
                                @endif
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold">{{ $item->product_name }}</div>
                                    <small class="text-muted">{{ $otherParty->name }}'s item</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if(in_array($trade->status, ['pending_shipping', 'shipped']))
                <div class="trade-card-block">
                    <h5 class="fw-bold mb-3">Shipping</h5>
                    @if($trade->isInitiator(Auth::id()))
                        @if($trade->initiator_shipping_address)
                            <p class="mb-1"><strong>Your address:</strong> {{ $trade->initiator_shipping_address['address'] ?? '' }}, {{ $trade->initiator_shipping_address['city'] ?? '' }}</p>
                            @if($trade->initiator_shipped_at)
                                <p class="mb-0 text-success">Shipped. Tracking: {{ $trade->initiator_tracking_number }}</p>
                            @else
                                <form action="{{ route('trading.trades.update-shipping', $trade->id) }}" method="post" class="mb-3">
                                    @csrf
                                    <input type="hidden" name="address" value="{{ $trade->initiator_shipping_address['address'] ?? '' }}">
                                    <input type="hidden" name="city" value="{{ $trade->initiator_shipping_address['city'] ?? '' }}">
                                    <input type="hidden" name="province" value="{{ $trade->initiator_shipping_address['province'] ?? '' }}">
                                    <input type="hidden" name="postal_code" value="{{ $trade->initiator_shipping_address['postal_code'] ?? '' }}">
                                    <input type="hidden" name="phone" value="{{ $trade->initiator_shipping_address['phone'] ?? '' }}">
                                </form>
                                <form action="{{ route('trading.trades.mark-shipped', $trade->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <input type="text" name="tracking_number" placeholder="Tracking number" class="form-control form-control-sm d-inline-block w-auto me-2" required>
                                    <button type="submit" class="btn btn-sm btn-primary">Mark shipped</button>
                                </form>
                            @endif
                        @else
                            <p class="text-muted">Add your shipping address and mark when shipped.</p>
                            <form action="{{ route('trading.trades.update-shipping', $trade->id) }}" method="post">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-12"><input type="text" name="address" class="form-control" placeholder="Address" required></div>
                                    <div class="col-6"><input type="text" name="city" class="form-control" placeholder="City" required></div>
                                    <div class="col-6"><input type="text" name="province" class="form-control" placeholder="Province" required></div>
                                    <div class="col-6"><input type="text" name="postal_code" class="form-control" placeholder="Postal code" required></div>
                                    <div class="col-6"><input type="text" name="phone" class="form-control" placeholder="Phone" required></div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Save address</button>
                            </form>
                        @endif
                    @else
                        @if($trade->participant_shipping_address)
                            <p class="mb-1"><strong>Your address:</strong> {{ $trade->participant_shipping_address['address'] ?? '' }}, {{ $trade->participant_shipping_address['city'] ?? '' }}</p>
                            @if($trade->participant_shipped_at)
                                <p class="mb-0 text-success">Shipped. Tracking: {{ $trade->participant_tracking_number }}</p>
                            @else
                                <form action="{{ route('trading.trades.mark-shipped', $trade->id) }}" method="post" class="d-inline">
                                    @csrf
                                    <input type="text" name="tracking_number" placeholder="Tracking number" class="form-control form-control-sm d-inline-block w-auto me-2" required>
                                    <button type="submit" class="btn btn-sm btn-primary">Mark shipped</button>
                                </form>
                            @endif
                        @else
                            <form action="{{ route('trading.trades.update-shipping', $trade->id) }}" method="post">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-12"><input type="text" name="address" class="form-control" placeholder="Address" required></div>
                                    <div class="col-6"><input type="text" name="city" class="form-control" placeholder="City" required></div>
                                    <div class="col-6"><input type="text" name="province" class="form-control" placeholder="Province" required></div>
                                    <div class="col-6"><input type="text" name="postal_code" class="form-control" placeholder="Postal code" required></div>
                                    <div class="col-6"><input type="text" name="phone" class="form-control" placeholder="Phone" required></div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Save address</button>
                            </form>
                        @endif
                    @endif
                </div>
            @endif

            @if($trade->status === 'shipped' || $trade->status === 'received')
                <div class="trade-card-block">
                    @if(($trade->isInitiator(Auth::id()) && !$trade->initiator_received_at) || ($trade->isParticipant(Auth::id()) && !$trade->participant_received_at))
                        <form action="{{ route('trading.trades.mark-received', $trade->id) }}" method="post" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">I received the item</button>
                        </form>
                    @endif
                </div>
            @endif

            @if($trade->canBeCompleted())
                <div class="trade-card-block">
                    <form action="{{ route('trading.trades.complete', $trade->id) }}" method="post" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Complete trade</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="trade-card-block">
                <h5 class="fw-bold mb-3">Actions</h5>
                @if($trade->conversation)
                    <a href="{{ route('trading.conversations.show', $trade->conversation) }}" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-chat-dots me-2"></i>Trade Chat
                    </a>
                @endif
                @if(!in_array($trade->status, ['completed', 'cancelled', 'disputed']))
                    <form action="{{ route('trading.trades.dispute', $trade->id) }}" method="post" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning w-100 mb-2">Open dispute</button>
                    </form>
                    <form action="{{ route('trading.trades.cancel', $trade->id) }}" method="post" onsubmit="return confirm('Cancel this trade?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Cancel trade</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
