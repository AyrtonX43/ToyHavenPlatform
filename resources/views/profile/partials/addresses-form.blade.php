<section class="mb-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>{{ __('Addresses') }}</h5>
                <p class="text-muted small mb-0 mt-2">{{ __('Manage your permanent and work addresses. Set one as default for orders.') }}</p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                <i class="bi bi-plus-circle me-1"></i>Add Address
            </button>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($user->addresses->count() > 0)
                <div class="row">
                    @foreach($user->addresses as $address)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 {{ $address->is_default ? 'border-primary' : '' }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            @if($address->is_default)
                                                <span class="badge bg-primary mb-2"><i class="bi bi-star-fill me-1"></i>Default</span>
                                            @endif
                                            <span class="badge bg-secondary mb-2">{{ ucfirst($address->type) }}</span>
                                            @if($address->label)
                                                <span class="badge bg-info mb-2">{{ $address->label }}</span>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-1">
                                            <form action="{{ route('profile.addresses.destroy', $address->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Address">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="mb-1"><strong>{{ $address->address }}</strong></p>
                                    <p class="mb-1 text-muted small">{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                                    @if($address->notes)
                                        <p class="mb-0 text-muted small"><i class="bi bi-info-circle me-1"></i>{{ $address->notes }}</p>
                                    @endif
                                    @if(!$address->is_default)
                                        <form action="{{ route('profile.addresses.set-default', $address->id) }}" method="POST" class="mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-star me-1"></i>Set as Default
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-geo-alt text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No addresses added yet. Add your first address to get started.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('profile.addresses.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Address Type</label>
                            <select name="type" class="form-select" required>
                                <option value="permanent">Permanent</option>
                                <option value="work">Work</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Label (Optional)</label>
                            <input type="text" name="label" class="form-control" placeholder="e.g., Home, Office">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Street Address <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required placeholder="Enter your street address"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Province <span class="text-danger">*</span></label>
                                <input type="text" name="province" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                                <input type="text" name="postal_code" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional details or delivery instructions"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defaultAdd">
                            <label class="form-check-label" for="defaultAdd">
                                Set as default address
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle add modal reopening on validation errors
    @if($errors->any() && request()->routeIs('profile.addresses.store'))
        var addModalElement = document.getElementById('addAddressModal');
        if (addModalElement) {
            var addModal = new bootstrap.Modal(addModalElement);
            addModal.show();
        }
    @endif
});
</script>
@endpush
