@extends('layouts.admin-new')

@section('title', 'User Details - ToyHaven')
@section('page-title', 'User Details: ' . $user->name)

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'seller' ? 'primary' : 'secondary') }} fs-6">
                            {{ ucfirst($user->role ?? 'customer') }}
                        </span>
                        @if($user->is_banned ?? false)
                            <span class="badge bg-danger fs-6">Banned</span>
                        @else
                            <span class="badge bg-success fs-6">Active</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary">{{ $stats['total_orders'] }}</h3>
                <small class="text-muted">Total Orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">₱{{ number_format($stats['total_spent'], 2) }}</h3>
                <small class="text-muted">Total Spent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info">{{ $stats['total_reviews'] }}</h3>
                <small class="text-muted">Reviews Given</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning">{{ $stats['wishlist_items'] }}</h3>
                <small class="text-muted">Wishlist Items</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Name:</strong><br>
                        {{ $user->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        {{ $user->email }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Phone:</strong><br>
                        {{ $user->phone ?? 'N/A' }}
                        @if($user->phone_verified_at)
                            <span class="badge bg-success">Verified</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Registered:</strong><br>
                        {{ $user->created_at->format('M d, Y h:i A') }}
                    </div>
                </div>
                @if($user->address || $user->city)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Address:</strong><br>
                        {{ $user->address ?? '' }}, {{ $user->city ?? '' }}, {{ $user->province ?? '' }} {{ $user->postal_code ?? '' }}
                    </div>
                </div>
                @endif
                @if($user->seller)
                <div class="row">
                    <div class="col-12">
                        <strong>Seller Account:</strong><br>
                        <a href="{{ route('admin.sellers.show', $user->seller->id) }}">{{ $user->seller->business_name }}</a>
                        <span class="badge bg-{{ $user->seller->verification_status === 'approved' ? 'success' : 'warning' }}">
                            {{ ucfirst($user->seller->verification_status) }}
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="{{ route('admin.orders.index', ['user' => $user->id]) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                    <td>₱{{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'warning' }}">
                                            {{ $order->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No orders yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Reviews</h5>
            </div>
            <div class="card-body">
                @forelse($user->reviews as $review)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>Product Review</strong>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }} text-warning"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="mb-1">{{ Str::limit($review->review_text, 100) }}</p>
                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <p class="text-muted">No reviews yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Actions</h5>
            </div>
            <div class="card-body">
                @if($user->is_banned ?? false)
                    <div class="alert alert-danger">
                        <strong>Status:</strong> Banned<br>
                        <small>Banned on: {{ $user->banned_at ? $user->banned_at->format('M d, Y h:i A') : 'N/A' }}</small>
                        @if($user->ban_reason)
                            <br><br><strong>Reason:</strong><br>
                            <small>{{ $user->ban_reason }}</small>
                        @endif
                        @if($user->bannedBy)
                            <br><small>Banned by: {{ $user->bannedBy->name }}</small>
                        @endif
                        @if($user->relatedReport)
                            <br><small><a href="{{ route('admin.reports.show', $user->related_report_id) }}">View Related Report</a></small>
                        @endif
                    </div>
                    <form action="{{ route('admin.users.unban', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i> Unban User
                        </button>
                    </form>
                @else
                    <div class="alert alert-success">
                        <strong>Status:</strong> Active
                    </div>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#banModal">
                        <i class="bi bi-x-circle me-1"></i> Ban User
                    </button>
                @endif
                
                <hr>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary w-100">Back to Users</a>
            </div>
        </div>
    </div>
</div>

<!-- Ban User Modal -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ban User Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will permanently ban this user from the platform. They will not be able to access their account, make purchases, or create listings.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ban Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Please provide a detailed reason for banning this user..." required></textarea>
                        <small class="text-muted">This reason will be sent to the user via email.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Report (Optional)</label>
                        <select name="report_id" class="form-select">
                            <option value="">No related report</option>
                            @foreach(\App\Models\Report::where('reportable_type', 'App\Models\User')->where('reportable_id', $user->id)->orWhere('reporter_id', $user->id)->orderBy('created_at', 'desc')->get() as $report)
                                <option value="{{ $report->id }}">Report #{{ $report->id }} - {{ $report->report_type }} ({{ $report->created_at->format('M d, Y') }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Link this ban to a specific report if applicable.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ban User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
