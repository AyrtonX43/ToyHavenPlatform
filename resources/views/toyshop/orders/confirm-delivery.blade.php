@extends('layouts.toyshop')

@push('styles')
<style>
    .confirm-delivery-actions {
        background: #fff;
        padding: 1.25rem 0 0;
        margin-top: 1.5rem;
        border-top: 2px solid #0d6efd;
    }
    @media (max-width: 575px) {
        .confirm-delivery-actions .d-flex { flex-direction: column; }
        .confirm-delivery-actions .btn { width: 100%; }
    }
    .proof-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }
    .proof-upload-area:hover, .proof-upload-area.dragover { border-color: #0d6efd; background: #f8f9fa; }
    .proof-preview-grid { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; }
    .proof-preview-item {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        flex-shrink: 0;
    }
    .proof-preview-item img { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
    .proof-preview-item .btn-delete {
        position: absolute; top: 4px; right: 4px;
        width: 28px; height: 28px; padding: 0; border-radius: 50%;
        background: rgba(220,53,69,0.9); color: white; border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.875rem;
    }
    .proof-preview-item .btn-view {
        position: absolute; bottom: 4px; left: 4px;
        width: 28px; height: 28px; padding: 0; border-radius: 50%;
        background: rgba(0,0,0,0.6); color: white; border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 0.75rem;
    }
    #proofFullscreen {
        position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999;
        display: none; align-items: center; justify-content: center; padding: 2rem;
    }
    #proofFullscreen.active { display: flex; }
    #proofFullscreen img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
    #proofFullscreen .btn-close-fs {
        position: absolute; top: 1rem; right: 1rem; background: rgba(255,255,255,0.2);
        color: white; border: none; width: 48px; height: 48px; border-radius: 50%;
        font-size: 1.5rem; cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container py-4 pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
        <div class="mb-4">
            <a href="{{ route('orders.show', $order->id) }}" class="text-primary text-decoration-none d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i>Back to Order
            </a>
        </div>

        <h1 class="h3 fw-bold mb-4">Confirm Delivery</h1>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
            <h2 class="h5 fw-semibold mb-2">Order #{{ $order->order_number }}</h2>
            <p class="text-muted small mb-4">Please upload a photo as proof of delivery</p>
            
            <div class="border-top pt-3">
                <h3 class="h6 fw-semibold mb-2">Order Items:</h3>
                @foreach($order->items as $item)
                <div class="d-flex align-items-center py-2">
                    <div class="flex-grow-1">
                        <p class="fw-medium mb-0">{{ $item->product_name }}</p>
                        <p class="small text-muted mb-0">Quantity: {{ $item->quantity }}</p>
                    </div>
                    <p class="fw-semibold mb-0">₱{{ number_format($item->subtotal, 2) }}</p>
                </div>
                @endforeach
            </div>
            </div>
        </div>

        <form action="{{ route('orders.confirm-delivery.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="card shadow-sm">
        <div class="card-body">
            @csrf
            
            <div class="mb-4">
                <label class="form-label fw-semibold">Proof of Delivery Photos <span class="text-danger">*</span></label>
                <p class="small text-muted mb-2">Upload 1–2 clear photos showing the delivered package (max 5MB each)</p>
                <input type="file" id="proofFileInput" accept="image/jpeg,image/png,image/jpg" multiple class="d-none">
                <div id="proofUploadArea" class="proof-upload-area" onclick="document.getElementById('proofFileInput').click()">
                    <i class="bi bi-cloud-arrow-up fs-1 text-muted"></i>
                    <p class="mb-0 mt-2 small text-muted">Click or drag to upload 1–2 photos</p>
                    <p class="mb-0 small text-muted">JPEG, PNG (max 5MB each)</p>
                </div>
                <div id="proofPreviewGrid" class="proof-preview-grid"></div>
                @error('proof_images')
                    <p class="text-danger small mt-1">{{ $message }}</p>
                @enderror
                @error('proof_images.*')
                    <p class="text-danger small mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Notes (Optional)</label>
                <textarea name="notes" rows="3" class="form-control" placeholder="Any additional comments about the delivery..."></textarea>
                <p class="small text-muted mt-1">Maximum 500 characters</p>
                @error('notes')
                    <p class="text-danger small mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="alert alert-info mb-4">
                <strong>Note:</strong> By confirming delivery, you acknowledge that you have received the order in good condition. 
                You will be able to review the product after confirmation.
            </div>

            <div class="confirm-delivery-actions">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-success btn-lg px-4 py-3">
                        <i class="bi bi-check2-circle me-2"></i>Confirm Delivery
                    </button>
                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary btn-lg px-4 py-3">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
        </form>
        </div>
    </div>
</div>

<div id="proofFullscreen" onclick="if(event.target===this) closeProofFullscreen()">
    <button type="button" class="btn-close-fs" onclick="closeProofFullscreen()" aria-label="Close">&times;</button>
    <img id="proofFullscreenImg" src="" alt="Proof of delivery">
</div>

@push('scripts')
<script>
(function() {
    var MAX_FILES = 2;
    var MAX_SIZE = 5 * 1024 * 1024; // 5MB
    var files = [];
    var input = document.getElementById('proofFileInput');
    var area = document.getElementById('proofUploadArea');
    var grid = document.getElementById('proofPreviewGrid');
    var form = document.querySelector('form[action*="confirm-delivery"]');
    var submitBtn = form.querySelector('button[type="submit"]');

    function renderPreviews() {
        grid.innerHTML = '';
        files.forEach(function(f, i) {
            var reader = new FileReader();
            reader.onload = (function(idx, file) {
                return function(e) {
                    var src = e.target.result;
                    var div = document.createElement('div');
                    div.className = 'proof-preview-item';
                    div.innerHTML = '<img src="' + src + '" alt="Preview ' + (idx+1) + '" data-index="' + idx + '">' +
                        '<button type="button" class="btn-delete" data-idx="' + idx + '" aria-label="Delete"><i class="bi bi-trash"></i></button>' +
                        '<button type="button" class="btn-view" data-src="' + src.replace(/"/g, '&quot;') + '" aria-label="View fullscreen"><i class="bi bi-zoom-in"></i></button>';
                    grid.appendChild(div);
                    div.querySelector('.btn-delete').addEventListener('click', function() { removeProofFile(idx); });
                    div.querySelector('.btn-view').addEventListener('click', function() { viewProofFullscreen(src); });
                    div.querySelector('img').addEventListener('click', function() { viewProofFullscreen(src); });
                };
            })(i, f);
            reader.readAsDataURL(f);
        });
    }

    function removeProofFile(idx) {
        files.splice(idx, 1);
        renderPreviews();
        if (files.length < MAX_FILES) area.style.display = '';
    }

    function viewProofFullscreen(src) {
        document.getElementById('proofFullscreenImg').src = src;
        document.getElementById('proofFullscreen').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    window.closeProofFullscreen = function() {
        document.getElementById('proofFullscreen').classList.remove('active');
        document.body.style.overflow = '';
    };

    function addFiles(newFiles) {
        for (var i = 0; i < newFiles.length && files.length < MAX_FILES; i++) {
            var f = newFiles[i];
            if (!f.type.match(/^image\/(jpeg|png|jpg)$/)) continue;
            if (f.size > MAX_SIZE) continue;
            files.push(f);
        }
        if (files.length >= MAX_FILES) area.style.display = 'none';
        renderPreviews();
    }

    input.addEventListener('change', function() {
        addFiles(Array.from(this.files));
        this.value = '';
    });

    area.addEventListener('dragover', function(e) {
        e.preventDefault();
        area.classList.add('dragover');
    });
    area.addEventListener('dragleave', function() { area.classList.remove('dragover'); });
    area.addEventListener('drop', function(e) {
        e.preventDefault();
        area.classList.remove('dragover');
        addFiles(Array.from(e.dataTransfer.files));
    });

    form.addEventListener('submit', function(e) {
        if (files.length < 1) {
            e.preventDefault();
            alert('Please upload at least 1 photo.');
            return false;
        }
        e.preventDefault();
        submitBtn.disabled = true;
        var fd = new FormData(form);
        fd.delete('proof_images[]');
        for (var i = 0; i < files.length; i++) fd.append('proof_images[]', files[i]);
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
        if (csrf) headers['X-CSRF-TOKEN'] = csrf.getAttribute('content');
        fetch(form.action, {
            method: 'POST',
            body: fd,
            headers: headers
        }).then(function(r) {
            if (r.redirected) {
                window.location.href = r.url;
                return;
            }
            if (!r.ok) return r.json().then(function(data) {
                submitBtn.disabled = false;
                var errs = data.errors || {};
                var msg = (errs['proof_images'] || errs['proof_images.0'] || ['Please check your uploads.'])[0];
                alert(msg);
            });
        }).catch(function() { submitBtn.disabled = false; });
    });
})();
</script>
@endpush
@endsection
