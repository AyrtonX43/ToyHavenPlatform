@extends('layouts.seller')

@section('title', 'My Orders - Seller Dashboard')

@section('page-title', 'Order Management')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Orders</h4>
        <p class="text-muted mb-0">Manage and track customer orders</p>
    </div>
    @if($orders->count() > 0)
        <button type="button" class="btn btn-outline-primary" id="bulkUpdateBtn" style="display: none;">
            <i class="bi bi-check2-square me-1"></i> Bulk Update Selected
        </button>
    @endif
</div>

@if($orders->count() > 0)
    <!-- Bulk Update Modal -->
    <div class="modal fade" id="bulkUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check2-square me-2"></i>Bulk Update Orders
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkUpdateForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <p>Update <span id="selectedCount" class="badge bg-primary">0</span> selected order(s) to:</p>
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="processing">Processing</option>
                                <option value="packed">Packed</option>
                                <option value="shipped">Shipped</option>
                                <option value="in_transit">In Transit</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                                <option value="delivered">Delivered</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Status update description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Orders
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-cart-check me-2"></i>Orders ({{ $orders->total() }})
            </h5>
            <div class="text-muted small">
                Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    @if($order->payment_status === 'paid' && !in_array($order->status, ['delivered', 'cancelled']))
                                        <input type="checkbox" class="form-check-input order-checkbox" name="order_ids[]" value="{{ $order->id }}">
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-primary">#{{ $order->order_number }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-person-circle me-2 text-muted"></i>
                                        {{ $order->user?->name ?? 'Walk-in Customer' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $order->items->count() }} item(s)
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-success">₱{{ number_format($order->total, 2) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'packed' => 'primary',
                                            'shipped' => 'primary',
                                            'in_transit' => 'info',
                                            'out_for_delivery' => 'warning',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $color = $statusColors[$order->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $order->getStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        <i class="bi bi-{{ $order->payment_status === 'paid' ? 'check-circle' : 'clock' }} me-1"></i>
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $order->created_at->format('M d, Y') }}<br>
                                        {{ $order->created_at->format('h:i A') }}
                                    </small>
                                </td>
                                <td>
                                    <a href="{{ route('seller.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
@else
    <!-- Empty State -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 mb-2">No orders yet</h4>
            <p class="text-muted mb-4">Orders from customers will appear here once they make purchases.</p>
            <a href="{{ route('seller.products.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Add Products to Get Started
            </a>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const bulkUpdateBtn = document.getElementById('bulkUpdateBtn');
    const bulkUpdateModal = new bootstrap.Modal(document.getElementById('bulkUpdateModal'));
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = this.checked;
                }
            });
            updateBulkUpdateButton();
        });
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateBulkUpdateButton();
            if (selectAll) {
                selectAll.checked = Array.from(checkboxes).every(c => c.checked);
            }
        });
    });
    
    function updateBulkUpdateButton() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        if (selected.length > 0 && bulkUpdateBtn) {
            bulkUpdateBtn.style.display = 'block';
        } else if (bulkUpdateBtn) {
            bulkUpdateBtn.style.display = 'none';
        }
    }
    
    if (bulkUpdateBtn) {
        bulkUpdateBtn.addEventListener('click', function() {
            const selected = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
            selectedCount.textContent = selected.length;
            
            const form = document.getElementById('bulkUpdateForm');
            form.action = '{{ route("seller.orders.bulkUpdate") }}';
            
            // Add hidden inputs for order IDs
            form.querySelectorAll('input[name="order_ids[]"]').forEach(input => input.remove());
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            bulkUpdateModal.show();
        });
    }
});
</script>
@endpush
@endsection
