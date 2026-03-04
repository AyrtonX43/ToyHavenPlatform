@extends('layouts.admin-new')

@section('title', 'Auction Details - Moderator')
@section('page-title', 'Auction: ' . Str::limit($auction->title, 50))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
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

            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h2 class="mb-0 fw-bold">{{ $auction->title }}</h2>
                                <span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'ended' ? 'secondary' : ($auction->status === 'pending_approval' ? 'warning' : 'danger')) }} fs-6">
                                    {{ ucwords(str_replace('_', ' ', $auction->status)) }}
                                </span>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="bi bi-person-circle me-1"></i>{{ $auction->user->name }}
                                <span class="mx-2">•</span>
                                <i class="bi bi-calendar3 me-1"></i>Created {{ $auction->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($canModerate && $auction->status === 'pending_approval')
                                <form action="{{ route('moderator.auctions.approve', $auction) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle me-1"></i>Approve
                                    </button>
                                </form>
                                <form action="{{ route('moderator.auctions.reject', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Reject this auction listing?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-circle me-1"></i>Reject
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('moderator.auctions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content (reuse admin structure) -->
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-images text-primary me-2"></i>Product Images</h5>
                        </div>
                        <div class="card-body">
                            @if($auction->images->where('image_type', 'standard')->count())
                                <div class="row g-2">
                                    @foreach($auction->images->where('image_type', 'standard') as $img)
                                        <div class="col-6">
                                            <div class="position-relative overflow-hidden rounded" style="height: 150px;">
                                                <img src="{{ asset('storage/' . $img->path) }}" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                                                @if($img->display_order === 0)
                                                    <span class="position-absolute top-0 start-0 badge bg-primary m-2">Primary</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-image fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No images uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($auction->verification_video_path)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-camera-video text-danger me-2"></i>Verification Video</h5>
                            </div>
                            <div class="card-body">
                                <div class="ratio ratio-16x9 rounded overflow-hidden">
                                    <video src="{{ asset('storage/' . $auction->verification_video_path) }}" controls style="object-fit: cover;"></video>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-file-text text-success me-2"></i>Description</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-justify">{{ $auction->description }}</p>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-gear text-secondary me-2"></i>Auction Settings</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Starting Bid</small>
                                        <h5 class="mb-0 text-primary">₱{{ number_format($auction->starting_bid, 2) }}</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Current Price</small>
                                        <h5 class="mb-0 text-success">₱{{ number_format($auction->getCurrentPrice(), 2) }}</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Bid Increment</small>
                                        <h5 class="mb-0">₱{{ number_format($auction->bid_increment, 2) }}</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="text-center p-3 bg-light rounded">
                                        <small class="text-muted d-block">Total Bids</small>
                                        <h5 class="mb-0">{{ $auction->bids_count }}</h5>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <i class="bi bi-calendar-event text-muted me-2"></i>
                                    <small class="text-muted">Start Date</small>
                                    <div class="fw-semibold">{{ $auction->start_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <i class="bi bi-calendar-check text-muted me-2"></i>
                                    <small class="text-muted">End Date</small>
                                    <div class="fw-semibold">{{ $auction->end_at?->format('M d, Y H:i') ?? 'TBD' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($auction->winner)
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-trophy-fill fs-3 me-3"></i>
                            <div>
                                <strong>Auction Winner</strong>
                                <div>{{ $auction->winner->name }} - ₱{{ number_format($auction->winning_amount ?? $auction->getCurrentPrice(), 2) }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-list-ol text-primary me-2"></i>Bid History</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th><i class="bi bi-person me-1"></i>Bidder</th>
                                    <th><i class="bi bi-currency-dollar me-1"></i>Amount</th>
                                    <th><i class="bi bi-clock me-1"></i>Date & Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auction->bids as $bid)
                                    <tr>
                                        <td>{{ $bid->user->name ?? 'N/A' }}</td>
                                        <td class="fw-semibold text-success">₱{{ number_format($bid->amount, 2) }}</td>
                                        <td>{{ $bid->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            @if($bid->is_winning)
                                                <span class="badge bg-success">Winning</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No bids placed yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
