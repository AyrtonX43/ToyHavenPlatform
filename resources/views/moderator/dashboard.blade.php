@extends('layouts.admin-new')

@section('title', 'Moderator Dashboard')
@section('page-title', 'Moderator Dashboard')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-3"><i class="bi bi-shield-check me-2"></i>Moderator Panel</h4>
                <p class="text-muted mb-4">Manage trade listings, view trades, and handle disputes. Use the sidebar to navigate.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('moderator.trade-listings.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-card-list display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Trade Listings</h5>
                                    <p class="card-text text-muted small mb-0">Review and approve or reject new trade listings.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('moderator.trades.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-arrow-left-right display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Trades</h5>
                                    <p class="card-text text-muted small mb-0">View and monitor active trades.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('moderator.trade-disputes.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-exclamation-triangle display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Trade Disputes</h5>
                                    <p class="card-text text-muted small mb-0">Handle trade disputes and resolution.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('moderator.conversation-reports.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-chat-dots display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Conversation Reports</h5>
                                    <p class="card-text text-muted small mb-0">Review reports on trade chat (product & seller).</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @if(auth()->user()->hasAuctionPermission('auctions_view'))
                    <div class="col-md-4">
                        <a href="{{ route('moderator.auctions.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-hammer display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Auctions</h5>
                                    <p class="card-text text-muted small mb-0">View and moderate auction listings.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                    @if(auth()->user()->hasAuctionPermission('auction_payments_moderate'))
                    <div class="col-md-4">
                        <a href="{{ route('moderator.auction-payments.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-credit-card display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Auction Payments</h5>
                                    <p class="card-text text-muted small mb-0">Release escrow and manage payments.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                    @if(auth()->user()->hasAuctionPermission('auction_sellers_moderate'))
                    <div class="col-md-4">
                        <a href="{{ route('moderator.auction-seller-profiles.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-person-badge display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Auction Sellers</h5>
                                    <p class="card-text text-muted small mb-0">Review seller profiles.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                    @if(auth()->user()->hasAuctionPermission('plans_manage'))
                    <div class="col-md-4">
                        <a href="{{ route('moderator.plans.index') }}" class="text-decoration-none">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <i class="bi bi-gem display-4 text-primary d-block mb-2"></i>
                                    <h5 class="card-title">Membership Plans</h5>
                                    <p class="card-text text-muted small mb-0">Edit plan price and description.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
