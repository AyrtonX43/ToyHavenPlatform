<section class="mb-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-grid-3x3-gap me-2"></i>{{ __('Category Preferences') }}
            </h5>
            <p class="text-muted small mb-0 mt-2">
                {{ __('Select your favorite toy categories to receive personalized product recommendations.') }}
            </p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.category-preferences.update') }}" id="categoryPreferencesForm">
                @csrf
                @method('patch')

                @if($categories->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">{{ __('Select Categories') }}</label>
                        <small class="text-muted d-block mb-3">You can select multiple categories</small>
                        
                        <div class="row g-3" id="categoryGrid">
                            @foreach($categories as $category)
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div class="category-preference-card">
                                        <input type="checkbox" 
                                               name="categories[]" 
                                               value="{{ $category->id }}" 
                                               id="profile_category_{{ $category->id }}"
                                               class="category-preference-checkbox d-none"
                                               {{ in_array($category->id, $user->categoryPreferences->pluck('id')->toArray()) ? 'checked' : '' }}>
                                        <label for="profile_category_{{ $category->id }}" 
                                               class="category-preference-label card h-100 border shadow-sm position-relative overflow-hidden">
                                            <!-- Selection Indicator -->
                                            <div class="category-preference-indicator">
                                                <i class="bi bi-check-circle-fill"></i>
                                            </div>
                                            
                                            <!-- Category Icon/Image (Flaticon animated when mapped) -->
                                            <div class="category-preference-image-wrapper">
                                                @php
                                                    $iconConfig = $category->getAnimatedIconConfig();
                                                    $iconPngUrl = $category->getAnimatedIconPngUrl();
                                                    $iconPageUrl = $iconConfig['url'] ?? null;
                                                @endphp
                                                @if($category->image)
                                                    <img src="{{ asset('storage/' . $category->image) }}" 
                                                         alt="{{ $category->name }}"
                                                         class="category-preference-image">
                                                @elseif($iconPngUrl)
                                                    <div class="category-flaticon-embed category-preference-flaticon" 
                                                         data-icon-id="{{ $iconConfig['id'] ?? '' }}" 
                                                         data-pack="{{ $iconConfig['pack'] ?? '' }}"
                                                         data-lottie-local="{{ asset('lottie/' . ($iconConfig['id'] ?? '') . '.json') }}">
                                                        <div class="category-lottie-container" aria-hidden="true"></div>
                                                        <img src="{{ $iconPngUrl }}" alt="{{ $category->name }}" class="category-preference-image category-animated-fallback" loading="lazy">
                                                    </div>
                                                @else
                                                    <div class="category-preference-icon-placeholder">
                                                        <i class="bi {{ $category->getDisplayIcon() }}"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Category Content -->
                                            <div class="category-preference-content">
                                                <h6 class="category-preference-name fw-bold mb-2">{{ $category->name }}</h6>
                                                @if($category->description)
                                                    <p class="category-preference-description text-muted small mb-0">
                                                        {{ Str::limit($category->description, 60) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @error('categories')
                            <div class="alert alert-danger mt-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>{{ __('Save Preferences') }}
                        </button>

                        @if (session('status') === 'category-preferences-updated')
                            <p class="text-success mb-0">
                                <i class="bi bi-check-circle me-1"></i>{{ __('Category preferences saved successfully!') }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        No categories available at the moment.
                    </div>
                @endif
            </form>
        </div>
    </div>
</section>

@push('styles')
<style>
    .category-preference-card {
        position: relative;
        height: 100%;
    }

    .category-preference-label {
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        padding: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .category-preference-label:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
    }

    .category-preference-image-wrapper {
        position: relative;
        height: 140px;
        overflow: hidden;
        background: #0d9488;
    }

    .category-preference-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .category-preference-label:hover .category-preference-image {
        transform: scale(1.05);
    }

    .category-preference-icon-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: white;
        opacity: 0.8;
    }

    /* Flaticon animated icon (profile) */
    .category-preference-flaticon {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #0d9488;
    }

    .category-preference-flaticon .category-lottie-container {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    .category-preference-flaticon .category-lottie-container canvas,
    .category-preference-flaticon .category-lottie-container svg {
        max-width: 85%;
        max-height: 85%;
        object-fit: contain;
    }

    .category-preference-flaticon .category-animated-fallback {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 0.75rem;
        z-index: 1;
    }

    .category-preference-flaticon.lottie-loaded .category-animated-fallback {
        opacity: 0;
        pointer-events: none;
    }

    .category-preference-content {
        padding: 1.25rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .category-preference-name {
        color: #1f2937;
        font-size: 1.1rem;
        transition: color 0.3s ease;
    }

    .category-preference-description {
        line-height: 1.5;
        font-size: 0.875rem;
    }

    /* Selection Indicator */
    .category-preference-indicator {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 30px;
        height: 30px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .category-preference-indicator i {
        color: #10b981;
        font-size: 1.25rem;
    }

    .category-preference-checkbox:checked + .category-preference-label {
        border: 2px solid #10b981 !important;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(52, 211, 153, 0.05) 100%);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.25) !important;
    }

    .category-preference-checkbox:checked + .category-preference-label .category-preference-indicator {
        opacity: 1;
        transform: scale(1);
    }

    .category-preference-checkbox:checked + .category-preference-label .category-preference-name {
        color: #10b981;
    }

    @media (max-width: 768px) {
        .category-preference-image-wrapper {
            height: 120px;
        }
        
        .category-preference-content {
            padding: 1rem;
        }
        
        .category-preference-name {
            font-size: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load Flaticon animated icons as Lottie (profile category preferences)
    if (typeof lottie !== 'undefined') {
        document.querySelectorAll('.category-preference-flaticon').forEach(function(wrapper) {
            var iconId = wrapper.getAttribute('data-icon-id');
            if (!iconId) return;
            var container = wrapper.querySelector('.category-lottie-container');
            if (!container) return;
            var localPath = wrapper.getAttribute('data-lottie-local');
            var cdnPath = 'https://cdn.flaticon.com/lottie/' + iconId + '/' + iconId + '.json';
            var pathsToTry = localPath ? [localPath, cdnPath] : [cdnPath];
            function tryLoad(pathIndex) {
                if (pathIndex >= pathsToTry.length) return;
                var anim = lottie.loadAnimation({
                    container: container,
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: pathsToTry[pathIndex]
                });
                anim.addEventListener('DOMLoaded', function() { wrapper.classList.add('lottie-loaded'); });
                anim.addEventListener('error', function() { tryLoad(pathIndex + 1); });
            }
            tryLoad(0);
        });
    }

    const checkboxes = document.querySelectorAll('.category-preference-checkbox');
    const form = document.getElementById('categoryPreferencesForm');
    
    // Add click animation
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.style.animation = 'none';
                setTimeout(() => {
                    label.style.animation = 'scaleIn 0.3s ease-out';
                }, 10);
            }
        });
    });
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.category-preference-checkbox:checked').length;
            
            // Allow saving even with 0 selections (user can clear all preferences)
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Saving...';
            }
        });
    }
});
</script>
@endpush
