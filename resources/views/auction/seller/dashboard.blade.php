@extends('layouts.toyshop')

@section('title', 'Auction Seller Dashboard - ToyHaven')

@push('styles')
<link href="{{ asset('css/auction.css') }}" rel="stylesheet">
<style>
    .seller-type-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: .8rem; font-weight: 600; }
    .seller-type-badge.individual { background: #dbeafe; color: #1e40af; }
    .seller-type-badge.business { background: #d1fae5; color: #065f46; }
    .stat-card { border-radius: 14px; border: none; transition: transform .2s; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
</style>
@endpush

@section('content')
<div class="auction-hero">
    <div class="container text-center">
        <h1 class="mb-2 fw-bold">
            <i class="bi bi-speedometer2 me-2"></i>Auction Seller Dashboard
        </h1>
        <div class="d-flex justify-content-center gap-2 mt-2">
            @if($isBusinessSeller)
                <span class="seller-type-badge business">
                    <i class="bi bi-shop"></i> Business Seller
                    @if($businessVerification)
                        @php
                            $info = is_array($businessVerification->business_info)
                                ? $businessVerification->business_info
                                : (is_string($businessVerification->business_info) ? json_decode($businessVerification->business_info, true) : []);
                        @endphp
                        @if(!empty($info['business_name']))
                            &mdash; {{ $info['business_name'] }}
                        @endif
                    @endif
                </span>
            @endif
            @if($isIndividualSeller)
                <span class="seller-type-badge individual">
                    <i class="bi bi-person-check"></i> Individual Seller
                </span>
            @endif
        </div>
    </div>
</div>

<div class="container py-4 pb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item active">Seller Dashboard</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Quick Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-broadcast"></i></div>
                    <div>
                        <div class="text-muted small">Active</div>
                        <div class="fs-4 fw-bold">{{ $quickStats['active'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <div class="text-muted small">Pending Approval</div>
                        <div class="fs-4 fw-bold">{{ $quickStats['pending_approval'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-flag-fill"></i></div>
                    <div>
                        <div class="text-muted small">Ended</div>
                        <div class="fs-4 fw-bold">{{ $quickStats['ended'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-truck"></i></div>
                    <div>
                        <div class="text-muted small">Needs Shipping</div>
                        <div class="fs-4 fw-bold">{{ $quickStats['pending_shipment'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $plan = auth()->user()->currentPlan();
        $isVip = $plan && (($plan->can_register_individual_seller ?? false) || ($plan->can_register_business_seller ?? false));
    @endphp

    {{-- Business Tools --}}
    <h4 class="mb-3 fw-bold"><i class="bi bi-tools me-2"></i>Seller Tools</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-4">
            @if($isVip)
                <a href="{{ route('auction.listings.create') }}" class="auction-shortcut">
                    <i class="bi bi-plus-circle fs-4 me-2 text-primary"></i>
                    <strong>Add Auction Listing</strong>
                    <span class="d-block small text-muted mt-1">Create a new auction</span>
                </a>
            @else
                <div class="auction-shortcut coming-soon">
                    <i class="bi bi-plus-circle fs-4 me-2 text-primary"></i>
                    <strong>Add Auction Listing</strong>
                    <span class="badge bg-secondary ms-2">VIP Only</span>
                    <span class="d-block small text-muted mt-1">Upgrade to VIP to create listings</span>
                </div>
            @endif
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('auction.listings.index') }}" class="auction-shortcut">
                <i class="bi bi-list-ul fs-4 me-2 text-primary"></i>
                <strong>My Listings</strong>
                <span class="d-block small text-muted mt-1">View and manage your auction listings</span>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('auction.seller.stats') }}" class="auction-shortcut">
                <i class="bi bi-graph-up fs-4 me-2 text-primary"></i>
                <strong>Seller Stats</strong>
                <span class="d-block small text-muted mt-1">View your auction analytics</span>
            </a>
        </div>
        @if($isIndividualSeller && !$isBusinessSeller && ($plan->can_register_business_seller ?? false))
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('auction.seller-registration.business') }}" class="auction-shortcut">
                    <i class="bi bi-shop fs-4 me-2 text-success"></i>
                    <strong>Upgrade to Business Seller</strong>
                    <span class="d-block small text-muted mt-1">Register your business for more features</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Sales Table --}}
    @if($sales->count() > 0)
        <h4 class="mb-3 fw-bold"><i class="bi bi-receipt me-2"></i>Recent Sales</h4>
        <div class="card auction-card border-0 mb-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Auction</th>
                            <th>Winner</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Delivery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('auction.show', $p->auction_id) }}" class="text-decoration-none">
                                        {{ Str::limit($p->auction?->title, 35) }}
                                    </a>
                                </td>
                                <td>{{ $p->winner?->name ?? 'N/A' }}</td>
                                <td class="fw-semibold">₱{{ number_format($p->amount, 2) }}</td>
                                <td>
                                    @if($p->status === 'pending')
                                        <span class="badge bg-warning text-dark">Awaiting Payment</span>
                                        @if($p->payment_deadline)
                                            <br><span class="small text-muted">Due: {{ $p->payment_deadline->format('M d, g:i A') }}</span>
                                        @endif
                                    @elseif($p->status === 'held')
                                        <span class="badge bg-info">Held in Escrow</span>
                                    @elseif($p->status === 'released')
                                        <span class="badge bg-success">Released</span>
                                    @elseif($p->status === 'paid')
                                        <span class="badge bg-primary">Paid</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($p->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->delivery_status === 'shipped')
                                        <span class="text-info small"><i class="bi bi-truck me-1"></i>Shipped</span>
                                        @if($p->tracking_number)
                                            <br><span class="small text-muted">{{ $p->tracking_number }}</span>
                                        @endif
                                    @elseif(in_array($p->delivery_status, ['delivered', 'confirmed']))
                                        <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Delivered</span>
                                    @else
                                        <span class="text-muted small">Pending shipment</span>
                                    @endif
                                </td>
                                <td>
                                    @if(
                                        !in_array($p->delivery_status ?? '', ['shipped', 'delivered', 'confirmed'])
                                        && in_array($p->status, ['paid', 'held'])
                                    )
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#shippedModal{{ $p->id }}">
                                            <i class="bi bi-truck me-1"></i>Ship
                                        </button>

                                        <div class="modal fade" id="shippedModal{{ $p->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content rounded-3 border-0 shadow">
                                                    <form action="{{ route('auction.payment.shipped', $p) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title fw-semibold">Mark as Shipped</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body pt-2">
                                                            <p class="text-muted small mb-3">Item: {{ Str::limit($p->auction?->title, 50) }}</p>
                                                            <label class="form-label">Tracking Number <span class="text-muted">(optional)</span></label>
                                                            <input type="text" name="tracking_number" class="form-control rounded-pill" placeholder="e.g. 1234567890">
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                                <i class="bi bi-check-lg me-1"></i>Mark Shipped
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif(in_array($p->delivery_status ?? '', ['shipped', 'delivered', 'confirmed']))
                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check me-1"></i>Shipped</span>
                                    @elseif($p->status === 'pending')
                                        <span class="text-muted small">Waiting for payment</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card border-0 mb-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                <p class="text-muted mb-0">No sales yet. Create a listing and start selling!</p>
            </div>
        </div>
    @endif

    <a href="{{ route('auction.index') }}" class="btn btn-outline-primary rounded-pill">
        <i class="bi bi-arrow-left me-1"></i>Back to Auction Hub
    </a>
</div>
@endsection
