@extends('layouts.admin-new')

@section('title', 'Auction Payments')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4 fw-bold"><i class="bi bi-currency-dollar me-2"></i>Auction Payments</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Held in Escrow</div>
                    <div class="fs-4 fw-bold text-info">₱{{ number_format($stats['total_held'], 2) }}</div>
                    <div class="small text-muted">{{ $stats['in_escrow'] }} payment(s)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Released</div>
                    <div class="fs-4 fw-bold text-success">₱{{ number_format($stats['total_released'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Awaiting Payment</div>
                    <div class="fs-4 fw-bold text-warning">{{ $stats['awaiting_payment'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Ready to Release</div>
                    <div class="fs-4 fw-bold text-primary">{{ $stats['ready_to_release'] }}</div>
                    <div class="small text-muted">Delivered, escrow can be released</div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ url()->current() }}" method="GET" class="mb-3">
        <select name="status" class="form-select form-select-sm w-auto d-inline-block" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="held" {{ request('status') === 'held' ? 'selected' : '' }}>Held (Escrow)</option>
            <option value="released" {{ request('status') === 'released' ? 'selected' : '' }}>Released</option>
            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
        </select>
    </form>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;"></th>
                        <th>Auction</th>
                        <th>Buyer</th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Delivery</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        @php $pImg = $p->auction?->images?->firstWhere('is_primary', true) ?? $p->auction?->images?->first(); @endphp
                        <tr>
                            <td>
                                @if($pImg)
                                    <img src="{{ asset('storage/' . $pImg->image_path) }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                                @else
                                    <div style="width:40px;height:40px;border-radius:6px;" class="bg-light d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('auction.show', $p->auction_id) }}" class="text-decoration-none fw-semibold" target="_blank">
                                    {{ Str::limit($p->auction?->title, 25) }}
                                </a>
                                <br><span class="text-muted" style="font-size:.7rem;">#{{ $p->id }}</span>
                            </td>
                            <td class="small">{{ $p->winner?->name ?? 'N/A' }}</td>
                            <td class="small">{{ $p->auction?->user?->name ?? 'N/A' }}</td>
                            <td class="fw-bold">₱{{ number_format($p->amount, 2) }}</td>
                            <td>
                                @if($p->status === 'pending')
                                    <span class="badge bg-warning text-dark">Awaiting</span>
                                    @if($p->payment_deadline && $p->payment_deadline->isPast())
                                        <br><span class="badge bg-danger mt-1" style="font-size:.6rem;">OVERDUE</span>
                                    @endif
                                @elseif($p->status === 'held')
                                    <span class="badge bg-info">In Escrow</span>
                                @elseif($p->status === 'released')
                                    <span class="badge bg-success">Released</span>
                                @elseif($p->status === 'paid')
                                    <span class="badge bg-primary">Paid</span>
                                @elseif($p->status === 'refunded')
                                    <span class="badge bg-danger">Refunded</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($p->status) }}</span>
                                @endif
                                @if($p->payment_method)
                                    <br><span class="text-muted" style="font-size:.65rem;text-transform:capitalize;">via {{ $p->payment_method }}</span>
                                @endif
                            </td>
                            <td>
                                @if($p->delivery_status === 'shipped')
                                    <span class="text-info small"><i class="bi bi-truck me-1"></i>Shipped</span>
                                    @if($p->tracking_number)
                                        <br><span class="text-muted" style="font-size:.65rem;">{{ $p->tracking_number }}</span>
                                    @endif
                                @elseif(in_array($p->delivery_status, ['delivered', 'confirmed']))
                                    <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Delivered</span>
                                @elseif(in_array($p->status, ['paid', 'held']))
                                    <span class="text-warning small"><i class="bi bi-box-seam me-1"></i>Pending Ship</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                @if($p->paid_at)
                                    {{ $p->paid_at->format('M d, Y') }}
                                @else
                                    {{ $p->created_at->format('M d, Y') }}
                                @endif
                            </td>
                            <td>
                                @if(in_array($p->status, ['paid', 'held']) && in_array($p->delivery_status, ['delivered', 'confirmed']))
                                    <form action="{{ route('admin.auction-payments.release', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Release ₱{{ number_format($p->amount, 2) }} to seller {{ $p->auction?->user?->name ?? '' }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                            <i class="bi bi-unlock me-1"></i>Release
                                        </button>
                                    </form>
                                @elseif($p->status === 'released')
                                    <span class="badge bg-light text-success border"><i class="bi bi-check-circle me-1"></i>Done</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No auction payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection
