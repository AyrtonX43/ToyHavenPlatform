<section class="mb-4 category-preferences-form">
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
                                            
                                            <!-- Category Content (name and description only - synced with welcome page) -->
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
        transform: translateY(-6px);
        box-shadow: 0 12px 30px rgba(14, 165, 233, 0.25) !important;
    }

    .category-preference-content {
        padding: 1.5rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .category-preference-name {
        color: #1e293b;
        font-size: 1.15rem;
        transition: color 0.3s ease;
    }

    .category-preference-description {
        line-height: 1.5;
        font-size: 0.875rem;
        color: #64748b;
    }

    /* Selection Indicator - sky blue (synced with welcome page) */
    .category-preference-indicator {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        z-index: 10;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
    }

    .category-preference-indicator i {
        color: #0284c7;
        font-size: 1.35rem;
    }

    .category-preference-checkbox:checked + .category-preference-label {
        border: 3px solid #0ea5e9 !important;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(56, 189, 248, 0.08) 100%);
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.25) !important;
    }

    .category-preference-checkbox:checked + .category-preference-label .category-preference-indicator {
        opacity: 1;
        transform: scale(1);
    }

    .category-preference-checkbox:checked + .category-preference-label .category-preference-name {
        color: #0284c7;
    }

    .category-preferences-form .btn-primary {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
        border-color: #0ea5e9 !important;
    }

    .category-preferences-form .btn-primary:hover {
        background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important;
        border-color: #0284c7 !important;
    }

    @media (max-width: 768px) {
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
<script>
document.addEventListener('DOMContentLoaded', function() {
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
