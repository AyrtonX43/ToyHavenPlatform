@extends('layouts.toyshop')

@section('title', 'Edit Auction Listing - ToyHaven')

@push('styles')
<style>
:root {
  --auction-primary: #0d9488;
  --auction-primary-hover: #0f766e;
  --auction-surface: #ffffff;
  --auction-border: #e2e8f0;
  --auction-text: #0f172a;
  --auction-muted: #64748b;
}
.auction-listing-card {
  background: var(--auction-surface);
  border-radius: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
  border: 1px solid var(--auction-border);
  margin-bottom: 1.5rem;
  overflow: hidden;
}
.auction-listing-card .card-header {
  background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
  color: #fff;
  padding: 1.25rem 1.5rem;
  font-weight: 600;
  border: none;
}
.auction-listing-card .card-body { padding: 1.5rem; }
.auction-section-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--auction-muted);
  margin-bottom: 1rem;
}
.duration-preset { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.duration-preset .preset-btn {
  flex: 1; min-width: 90px; padding: 0.75rem 1rem;
  border: 2px solid var(--auction-border); border-radius: 12px; background: #fff;
  cursor: pointer; transition: all 0.2s; text-align: center; font-size: 0.875rem; font-weight: 500;
}
.duration-preset .preset-btn:hover { border-color: var(--auction-primary); background: #f0fdfa; }
.duration-preset .preset-btn.active { border-color: var(--auction-primary); background: #ccfbf1; color: var(--auction-primary-hover); }
.image-zone { border: 2px dashed #cbd5e1; border-radius: 14px; background: #fafbfc; padding: 2rem; text-align: center; transition: all 0.2s; }
.image-zone:hover, .image-zone.dragover { border-color: var(--auction-primary); background: #f0fdfa; }
.image-preview-list { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; margin-top: 1rem; min-height: 60px; }
.image-preview-item { position: relative; flex-shrink: 0; cursor: grab; }
.image-preview-item:active { cursor: grabbing; }
.image-preview-item.dragging { opacity: 0.5; }
.image-preview-item img { width: 92px; height: 92px; object-fit: cover; border-radius: 10px; border: 2px solid var(--auction-border); display: block; cursor: zoom-in; transition: transform 0.2s; }
.image-preview-item img:hover { transform: scale(1.03); }
.image-preview-item .thumb-badge { position: absolute; top: -6px; left: -6px; z-index: 2; width: 26px; height: 26px; padding: 0; border-radius: 50%; font-size: 12px; line-height: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
.image-preview-item .btn-remove { position: absolute; top: -8px; right: -8px; width: 26px; height: 26px; padding: 0; border-radius: 50%; font-size: 14px; line-height: 1; z-index: 2; box-shadow: 0 2px 8px rgba(0,0,0,0.15); background: #dc2626; color: #fff; border: none; cursor: pointer; }
.image-preview-item .btn-remove:hover { background: #b91c1c; }
.category-select-btn { min-height: 48px; text-align: left; border-radius: 10px; border: 1px solid var(--auction-border); background: #fff; padding: 0.6rem 1rem; width: 100%; display: flex; align-items: center; justify-content: space-between; }
.category-select-btn:hover { background: #fafafa; border-color: #cbd5e1; }
#categoryModal .form-check { padding: 0.75rem 1.25rem; margin: 0; }
#categoryModal .form-check:hover { background: #f8fafc; }
#imageLightboxModal .modal-dialog { max-width: 95vw; }
#imageLightboxModal .modal-content { background: transparent; border: none; }
#imageLightboxModal .modal-body img { max-width: 100%; max-height: 85vh; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
.duration-custom-group input#durationHours { max-width: 6rem; }
</style>
@endpush

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('auction.index') }}">Auction</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.seller.dashboard') }}">Seller Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('auction.listings.index') }}">My Listings</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="auction-listing-card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Auction Listing</h4>
                    <p class="mb-0 small opacity-90">{{ Str::limit($listing->title, 50) }}</p>
                </div>
                <a href="{{ route('auction.listings.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            @if($listing->rejection_reason)
                <div class="alert alert-warning rounded-3 mb-4">
                    <strong>Feedback from reviewer:</strong><br>{{ $listing->rejection_reason }}
                </div>
            @endif

            <form action="{{ route('auction.listings.update', $listing) }}" method="POST" enctype="multipart/form-data" id="editForm">
                @csrf
                @method('PUT')

                <div class="auction-section-label">Item Details</div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg rounded-3 @error('title') is-invalid @enderror"
                        value="{{ old('title', $listing->title) }}" required maxlength="255">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control rounded-3 @error('description') is-invalid @enderror" rows="4"
                        maxlength="5000">{{ old('description', $listing->description) }}</textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Condition <span class="text-danger">*</span></label>
                    <select name="condition" class="form-select form-select-lg rounded-3 @error('condition') is-invalid @enderror" required>
                        @foreach(\App\Models\Auction::CONDITIONS as $value => $label)
                            <option value="{{ $value }}" {{ old('condition', $listing->condition ?? 'good') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="auction-section-label mt-4">Photos</div>
                <div class="mb-4">
                    <div id="existingPreviews" class="image-preview-list mb-3"></div>
                    <div class="image-zone" id="imageZone">
                        <input type="file" name="images[]" id="imageInput" accept="image/jpeg,image/png,image/jpg,image/webp" multiple class="d-none">
                        <p class="mb-0 text-muted"><i class="bi bi-cloud-arrow-up fs-2 d-block mb-2"></i>Add more images (drag & drop or click)</p>
                        <button type="button" class="btn btn-primary rounded-3 px-4 mt-2" id="uploadBtn"><i class="bi bi-upload me-2"></i>Choose files</button>
                    </div>
                    <div id="newPreviews" class="image-preview-list mt-3"></div>
                    <p class="small text-muted mt-2">★ = thumbnail. Drag to reorder. Max 10 total.</p>
                    <input type="hidden" name="thumbnail_index" id="thumbnailIndex" value="0">
                    <input type="hidden" name="image_order" id="imageOrder" value="">
                </div>

                <div class="auction-section-label mt-4">Pricing</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Starting Bid (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="starting_bid" class="form-control rounded-3 @error('starting_bid') is-invalid @enderror"
                            value="{{ old('starting_bid', $listing->starting_bid) }}" required min="1" step="0.01" {{ !$listing->isDraft() ? 'readonly' : '' }}>
                        @error('starting_bid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reserve Price (₱)</label>
                        <input type="number" name="reserve_price" class="form-control rounded-3 @error('reserve_price') is-invalid @enderror"
                            value="{{ old('reserve_price', $listing->reserve_price) }}" min="0" step="0.01" placeholder="Optional" {{ !$listing->isDraft() ? 'readonly' : '' }}>
                        @error('reserve_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bid Increment (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="bid_increment" class="form-control rounded-3 @error('bid_increment') is-invalid @enderror"
                            value="{{ old('bid_increment', $listing->bid_increment) }}" required min="1" step="0.01" {{ !$listing->isDraft() ? 'readonly' : '' }}>
                        @error('bid_increment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="auction-section-label">Duration</div>
                <div class="mb-4">
                    <div class="duration-preset mb-3" id="durationPresets" style="{{ !$listing->isDraft() ? 'pointer-events: none; opacity: 0.7;' : '' }}">
                        <button type="button" class="preset-btn" data-hours="24">24 hrs</button>
                        <button type="button" class="preset-btn" data-hours="72">3 days</button>
                        <button type="button" class="preset-btn" data-hours="168">1 week</button>
                        <button type="button" class="preset-btn" data-hours="336">2 weeks</button>
                        <button type="button" class="preset-btn" data-hours="720">30 days</button>
                    </div>
                    <div class="input-group duration-custom-group">
                        <span class="input-group-text rounded-start-3">Custom</span>
                        <input type="number" name="duration_hours" id="durationHours" class="form-control rounded-end-3 @error('duration_hours') is-invalid @enderror"
                            value="{{ old('duration_hours', $listing->duration_hours ?? 336) }}" required min="1" max="720" inputmode="numeric" pattern="[0-9]*" {{ !$listing->isDraft() ? 'readonly' : '' }}>
                        <span class="input-group-text">hours</span>
                    </div>
                    <small class="text-muted d-block mt-1">1–720 hours (30 days max). No over 30 days.</small>
                    @error('duration_hours')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="auction-section-label">Category</div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Categories <span class="text-muted fw-normal">(select 1–3)</span></label>
                    <button type="button" class="category-select-btn" id="categorySelectBtn" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <span id="categorySelectedText">Select categories...</span>
                        <i class="bi bi-chevron-down text-muted"></i>
                    </button>
                    <div id="categoryHiddenContainer"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg rounded-3 px-4">
                    <i class="bi bi-check-lg me-2"></i>Save Changes
                </button>
                <a href="{{ route('auction.listings.index') }}" class="btn btn-outline-secondary rounded-3 ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

{{-- Category modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3 border-0 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">Select categories (up to 3)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                @foreach($categories as $c)
                <label class="form-check category-option border-bottom d-block" data-id="{{ $c->id }}" data-name="{{ $c->name }}">
                    <input type="checkbox" class="form-check-input" value="{{ $c->id }}">
                    <span class="form-check-label">{{ $c->name }}</span>
                </label>
                @endforeach
            </div>
            <div class="modal-footer border-top">
                <span class="text-muted small me-auto" id="categoryCountHint">Select 1–3 categories</span>
                <button type="button" class="btn btn-primary rounded-3" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

{{-- Image lightbox --}}
<div class="modal fade" id="imageLightboxModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 position-absolute top-0 end-0 z-2">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img src="" alt="Full view" id="lightboxImage" class="img-fluid rounded-3 shadow">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
  var existingImages = @json($listing->images->map(function($i) { return ['id' => $i->id, 'path' => asset('storage/' . $i->image_path), 'is_primary' => (bool) $i->is_primary]; }));
  var imageOrder = existingImages.map(function(i) { return i.id; });
  var thumbnailIdx = 0;
  for (var k = 0; k < existingImages.length; k++) { if (existingImages[k].is_primary) { thumbnailIdx = k; break; } }
  var newFiles = [];

  var durationInput = document.getElementById('durationHours');
  var presets = document.querySelectorAll('#durationPresets .preset-btn');
  var dh = parseInt(durationInput.value, 10) || 336;
  presets.forEach(function(b) {
    if (parseInt(b.dataset.hours, 10) === dh) b.classList.add('active');
    b.addEventListener('click', function() {
      presets.forEach(function(x) { x.classList.remove('active'); });
      this.classList.add('active');
      durationInput.value = this.dataset.hours;
    });
  });
  function clampDuration() {
    var v = parseInt(durationInput.value, 10);
    if (isNaN(v) || v < 1) v = 1;
    if (v > 720) v = 720;
    durationInput.value = v;
    presets.forEach(function(b) { b.classList.toggle('active', parseInt(b.dataset.hours, 10) === v); });
  }
  durationInput.addEventListener('input', clampDuration);
  durationInput.addEventListener('change', clampDuration);
  durationInput.addEventListener('blur', clampDuration);
  clampDuration();

  var categorySelectedText = document.getElementById('categorySelectedText');
  var categoryContainer = document.getElementById('categoryHiddenContainer');
  var oldCatIds = @json(old('category_ids', $listing->category_ids ?: ($listing->category_id ? [$listing->category_id] : [])));
  document.querySelectorAll('#categoryModal .category-option').forEach(function(opt) {
    var id = opt.dataset.id;
    if (oldCatIds.indexOf(parseInt(id, 10)) >= 0) opt.querySelector('input').checked = true;
    opt.querySelector('input').addEventListener('change', function() {
      var c = document.querySelectorAll('#categoryModal input:checked');
      if (c.length > 3) this.checked = false;
      syncCategory();
    });
  });
  function syncCategory() {
    var checked = document.querySelectorAll('#categoryModal input:checked');
    var ids = Array.from(checked).map(function(x) { return x.value; });
    categoryContainer.innerHTML = '';
    ids.forEach(function(id) {
      var h = document.createElement('input');
      h.type = 'hidden'; h.name = 'category_ids[]'; h.value = id;
      categoryContainer.appendChild(h);
    });
    var names = Array.from(checked).map(function(x) { return x.closest('label').dataset.name || ''; }).filter(Boolean);
    categorySelectedText.textContent = names.length ? names.join(', ') : 'Select categories...';
    document.getElementById('categoryCountHint').textContent = ids.length ? ids.length + ' selected' : 'Select 1–3 categories';
  }
  syncCategory();

  function showLightbox(src) {
    var m = document.getElementById('imageLightboxModal');
    var img = document.getElementById('lightboxImage');
    if (m && img) { img.src = src; if (typeof bootstrap !== 'undefined' && bootstrap.Modal) new bootstrap.Modal(m).show(); }
  }

  function renderExisting() {
    var container = document.getElementById('existingPreviews');
    container.innerHTML = '';
    imageOrder.forEach(function(id, idx) {
      var imgData = existingImages.find(function(i) { return i.id == id; });
      if (!imgData) return;
      var div = document.createElement('div');
      div.className = 'image-preview-item';
      div.dataset.id = id;
      var img = document.createElement('img');
      img.src = imgData.path;
      img.addEventListener('click', function() { showLightbox(imgData.path); });
      var star = document.createElement('button');
      star.type = 'button';
      star.className = 'btn btn-sm ' + (idx === thumbnailIdx ? 'btn-warning' : 'btn-outline-secondary') + ' thumb-badge';
      star.innerHTML = '★';
      star.title = 'Set as thumbnail';
      star.onclick = function(e) { e.preventDefault(); thumbnailIdx = idx; updateHidden(); renderExisting(); renderNew(); };
      var remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'btn btn-remove';
      remove.innerHTML = '×';
      remove.onclick = function() {
        imageOrder = imageOrder.filter(function(x) { return x != id; });
        if (thumbnailIdx >= imageOrder.length) thumbnailIdx = Math.max(0, imageOrder.length - 1);
        updateHidden();
        renderExisting();
        renderNew();
      };
      div.appendChild(star);
      div.appendChild(img);
      div.appendChild(remove);
      container.appendChild(div);

      div.draggable = true;
      div.addEventListener('dragstart', function(ev) { ev.dataTransfer.setData('text/plain', 'ex:' + idx); div.classList.add('dragging'); });
      div.addEventListener('dragend', function() { div.classList.remove('dragging'); });
      div.addEventListener('dragover', function(ev) { ev.preventDefault(); });
      div.addEventListener('drop', function(ev) {
        ev.preventDefault();
        var raw = ev.dataTransfer.getData('text/plain');
        var fromIdx = raw.startsWith('ex:') ? parseInt(raw.slice(3), 10) : -1;
        if (fromIdx >= 0 && fromIdx !== idx) {
          var arr = imageOrder.slice();
          var it = arr.splice(fromIdx, 1)[0];
          arr.splice(idx, 0, it);
          imageOrder = arr;
          if (thumbnailIdx === fromIdx) thumbnailIdx = idx;
          else if (thumbnailIdx === idx) thumbnailIdx = fromIdx;
          else if (fromIdx < thumbnailIdx && idx >= thumbnailIdx) thumbnailIdx--;
          else if (fromIdx > thumbnailIdx && idx <= thumbnailIdx) thumbnailIdx++;
          updateHidden();
          renderExisting();
        }
      });
    });
  }

  function renderNew() {
    var container = document.getElementById('newPreviews');
    container.innerHTML = '';
    newFiles.forEach(function(f, i) {
      var div = document.createElement('div');
      div.className = 'image-preview-item';
      div.dataset.newIdx = i;
      var img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.addEventListener('click', function() { showLightbox(img.src); });
      var idx = imageOrder.length + i;
      var star = document.createElement('button');
      star.type = 'button';
      star.className = 'btn btn-sm ' + (idx === thumbnailIdx ? 'btn-warning' : 'btn-outline-secondary') + ' thumb-badge';
      star.innerHTML = '★';
      star.onclick = function(e) { e.preventDefault(); thumbnailIdx = idx; updateHidden(); renderExisting(); renderNew(); };
      var remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'btn btn-remove';
      remove.innerHTML = '×';
      remove.onclick = function() {
        newFiles.splice(i, 1);
        if (thumbnailIdx >= imageOrder.length + newFiles.length) thumbnailIdx = Math.max(0, imageOrder.length + newFiles.length - 1);
        updateFileInput();
        updateHidden();
        renderNew();
      };
      div.appendChild(star);
      div.appendChild(img);
      div.appendChild(remove);
      container.appendChild(div);
    });
    updateHidden();
  }

  function updateHidden() {
    document.getElementById('thumbnailIndex').value = thumbnailIdx;
    document.getElementById('imageOrder').value = imageOrder.join(',');
  }

  var imageZone = document.getElementById('imageZone');
  var imageInput = document.getElementById('imageInput');
  var uploadBtn = document.getElementById('uploadBtn');

  imageZone.addEventListener('click', function(e) {
    if (e.target === uploadBtn || uploadBtn.contains(e.target)) return;
    imageInput.click();
  });
  uploadBtn.addEventListener('click', function(e) { e.stopPropagation(); imageInput.click(); });
  imageZone.addEventListener('dragover', function(e) { e.preventDefault(); imageZone.classList.add('dragover'); });
  imageZone.addEventListener('dragleave', function(e) { e.preventDefault(); imageZone.classList.remove('dragover'); });
  imageZone.addEventListener('drop', function(e) {
    e.preventDefault();
    imageZone.classList.remove('dragover');
    var files = Array.from(e.dataTransfer.files || []).filter(function(f) { return f.type.indexOf('image/') === 0; });
    addFiles(files);
  });
  imageInput.addEventListener('change', function() {
    addFiles(Array.from(this.files || []));
    this.value = '';
  });

  function addFiles(files) {
    var total = imageOrder.length + newFiles.length;
    var space = 10 - total;
    files = files.slice(0, space);
    newFiles = newFiles.concat(files);
    if (imageOrder.length + newFiles.length > 10) newFiles = newFiles.slice(0, 10 - imageOrder.length);
    updateFileInput();
    renderNew();
  }

  function updateFileInput() {
    var dt = new DataTransfer();
    newFiles.forEach(function(f) { dt.items.add(f); });
    imageInput.files = dt.files;
  }

  renderExisting();

  document.getElementById('editForm').addEventListener('submit', function(ev) {
    updateFileInput();
    updateHidden();
    if (imageOrder.length + newFiles.length < 1) {
      ev.preventDefault();
      alert('At least one image is required.');
      return false;
    }
  });
})();
</script>
@endpush
