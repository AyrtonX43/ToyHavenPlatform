@extends('layouts.admin-new')

@section('title', 'Products Management - ToyHaven')
@section('page-title', 'Product Moderation')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div></div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.products.pending') }}" class="btn btn-warning">
            <i class="bi bi-hourglass-split me-1"></i> Products Requesting Approval
        </a>
        <a href="{{ route('admin.products.approved') }}" class="btn btn-success">
            <i class="bi bi-check2-square me-1"></i> Approved Products
        </a>
        <a href="{{ route('admin.products.rejected') }}" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i> Rejected Products
        </a>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Products</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Product name, SKU...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="sold_out" {{ request('status') == 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Products ({{ $products->total() }})</h5>
    </div>
    <div class="card-body">
        <form id="bulkForm" method="POST" action="{{ route('admin.products.bulk-action') }}">
            @csrf
            <div class="mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">Select All</button>
                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirmBulkSubmit(this.form, 'approve')">Approve Selected</button>
                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirmBulkSubmit(this.form, 'reject')">Reject Selected</button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAll"></th>
                            <th>Product</th>
                            <th>Seller</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    @if($product->status === 'pending')
                                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox">
                                    @else
                                        <span class="text-muted" title="Only pending products can be approved/rejected">—</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $product->name }}</strong><br>
                                    <small class="text-muted">SKU: {{ $product->sku }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sellers.show', $product->seller_id) }}">{{ $product->seller->business_name }}</a>
                                </td>
                                <td>{{ $product->category->name }}</td>
                                <td>₱{{ number_format($product->price, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $product->stock_quantity > 0 ? 'success' : 'danger' }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $product->status === 'active' ? 'success' : ($product->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>
                                <td>{{ $product->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No products found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        <div class="mt-3">{{ $products->links() }}</div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var selectAllCheckbox = document.getElementById('selectAll');
    var selectAllBtn = document.getElementById('selectAllBtn');
    var checkboxes = document.querySelectorAll('.product-checkbox');

    function updateSelectAllState() {
        if (!selectAllCheckbox) return;
        var boxes = document.querySelectorAll('.product-checkbox');
        if (boxes.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            return;
        }
        var checked = document.querySelectorAll('.product-checkbox:checked').length;
        selectAllCheckbox.checked = checked === boxes.length;
        selectAllCheckbox.indeterminate = checked > 0 && checked < boxes.length;
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.product-checkbox').forEach(function(cb) { cb.checked = checked; });
        });
    }

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            var allChecked = document.querySelectorAll('.product-checkbox:checked').length === document.querySelectorAll('.product-checkbox').length;
            document.querySelectorAll('.product-checkbox').forEach(function(cb) { cb.checked = !allChecked; });
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = !allChecked;
                selectAllCheckbox.indeterminate = false;
            }
        });
    }

    document.querySelectorAll('.product-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateSelectAllState);
    });
    updateSelectAllState();
})();

function confirmBulkSubmit(form, action) {
    var checked = form.querySelectorAll('.product-checkbox:checked');
    if (!checked.length) {
        alert('Please select at least one product');
        return false;
    }
    return confirm('Are you sure you want to ' + action + ' ' + checked.length + ' product(s)?');
}
</script>
@endpush
@endsection
