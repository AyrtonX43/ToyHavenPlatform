@extends('layouts.toyshop')

@section('title', 'Welcome to ToyHaven!')

@section('content')
<script>
    // Add classes to body and html to ensure background coverage
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('category-selection-body');
        document.documentElement.classList.add('category-selection-html');
    });
</script>
<div class="category-selection-wrapper">
    <!-- Animated Background -->
    <div class="animated-background"></div>
    
    <div class="container py-5 position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <!-- Welcome Header with Animation -->
                <div class="welcome-header text-center mb-5 reveal">
                    <div class="welcome-icon animate-float mb-4">
                        <i class="bi bi-emoji-smile-fill welcome-icon-sky" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-primary mb-3 animate-fade-in-up">
                        Welcome to ToyHaven, <span class="text-gradient">{{ Auth::user()->name }}!</span>
                    </h1>
                    <p class="lead welcome-subtext animate-fade-in-up" style="animation-delay: 0.2s;">
                        Help us personalize your experience by selecting the toy categories you're interested in.
                        <br>We'll show you products tailored just for you!
                        <br><small class="welcome-optional-hint mt-2 d-block">Don't worry, this is optional - you can skip and set it up later in your Profile Settings!</small>
                    </p>
                    <div class="progress-indicator mt-4 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <div class="progress-bar-custom">
                            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                        </div>
                        <p class="welcome-progress-text small mt-2 mb-0">
                            <span id="selectedCount">0</span> of <span id="totalCategories">{{ $categories->count() }}</span> categories selected
                        </p>
                    </div>
                </div>

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show reveal" role="alert" style="animation-delay: 0.3s;">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Category Selection Form -->
                <form method="POST" action="{{ route('category-preferences.store') }}" id="categoryForm">
                    @csrf
                    
                    <div class="card shadow-lg border-0 reveal" style="animation-delay: 0.2s; border-radius: 20px; overflow: hidden;">
                        <div class="card-header-custom">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h4 class="mb-1 fw-bold">
                                        <i class="bi bi-grid-3x3-gap me-2"></i>Select Your Favorite Toy Categories
                                    </h4>
                                    <small class="card-header-hint">This step is optional. Select categories for personalized recommendations, or skip and set it up later in Profile Settings.</small>
                                </div>
                                <div class="selection-badge">
                                    <span class="badge selection-badge-sky px-3 py-2" id="selectionBadge">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <span id="badgeCount">0</span> Selected
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            @if($categories->count() > 0)
                                <div class="row g-4" id="categoryGrid">
                                    @foreach($categories as $index => $category)
                                        <div class="col-lg-4 col-md-6 col-sm-6 category-item" 
                                             style="animation-delay: {{ ($index * 0.1) + 0.3 }}s;">
                                            <div class="category-card-wrapper">
                                                <input type="checkbox" 
                                                       name="categories[]" 
                                                       value="{{ $category->id }}" 
                                                       id="category_{{ $category->id }}"
                                                       class="category-checkbox d-none">
                                                <label for="category_{{ $category->id }}" 
                                                       class="category-label card h-100 border-0 shadow-sm position-relative overflow-hidden">
                                                    <!-- Selection Indicator -->
                                                    <div class="selection-indicator">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </div>
                                                    
                                                    <!-- Category Content (name and description only) -->
                                                    <div class="category-content">
                                                        <h5 class="category-name fw-bold mb-2">{{ $category->name }}</h5>
                                                        @if($category->description)
                                                            <p class="category-description text-muted small mb-0">
                                                                {{ Str::limit($category->description, 80) }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Hover Effect -->
                                                    <div class="category-hover-effect"></div>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @error('categories')
                                    <div class="alert alert-danger mt-4 reveal animate-shake">
                                        <i class="bi bi-exclamation-triangle me-2"></i>{{ $message }}
                                    </div>
                                @enderror

                                <!-- Submit Button Section -->
                                <div class="text-center mt-5 reveal" style="animation-delay: 0.6s;">
                                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center align-items-center mb-3">
                                        <button type="submit" class="btn btn-lg px-5 py-3 submit-button" id="submitBtn" disabled>
                                            <span class="button-content">
                                                <i class="bi bi-arrow-right me-2"></i>
                                                <span>Continue to Suggested Products</span>
                                            </span>
                                            <span class="button-loader d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                Processing...
                                            </span>
                                        </button>
                                        <button type="button" class="btn btn-skip-bottom btn-lg px-5 py-3" data-bs-toggle="modal" data-bs-target="#skipModal">
                                            <i class="bi bi-skip-forward me-2"></i>
                                            <span>Skip This Step</span>
                                        </button>
                                    </div>
                                    <p class="welcome-footer-hint small mb-0">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>This step is optional.</strong> Select categories for personalized recommendations, or skip and set it up later in your <a href="{{ route('profile.edit') }}" class="text-decoration-underline profile-link-sky">Profile Settings</a>.
                                    </p>
                                </div>
                            @else
                                <div class="alert alert-warning text-center reveal">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No categories available at the moment. Please contact support.
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Sky blue and white theme - light background for text visibility */
    body:has(.category-selection-wrapper),
    html:has(.category-selection-wrapper) {
        margin: 0 !important;
        padding: 0 !important;
        background: linear-gradient(135deg, #e0f4ff 0%, #f0f9ff 50%, #ffffff 100%) !important;
        overflow-x: hidden;
        height: 100%;
        width: 100%;
    }

    /* Fallback for browsers that don't support :has() */
    body.category-selection-body,
    html.category-selection-html {
        margin: 0 !important;
        padding: 0 !important;
        background: linear-gradient(135deg, #e0f4ff 0%, #f0f9ff 50%, #ffffff 100%) !important;
        overflow-x: hidden;
        height: 100%;
        width: 100%;
    }

    /* Category Selection Wrapper - sky blue and white theme */
    .category-selection-wrapper {
        position: relative;
        min-height: 100vh;
        width: 100%;
        background: linear-gradient(135deg, #e0f4ff 0%, #f0f9ff 50%, #ffffff 100%);
        background-attachment: fixed;
        padding: 2rem 0;
        margin: 0;
        overflow-x: hidden;
    }

    /* Override any Bootstrap or default styles that might cause white lines */
    .category-selection-wrapper::before,
    .category-selection-wrapper::after {
        display: none;
    }

    /* Override any Bootstrap or default styles that might cause white lines */
    .category-selection-wrapper::before,
    .category-selection-wrapper::after {
        display: none;
    }

    .animated-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        min-height: 100vh;
        background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(14,165,233,0.15)"/></svg>');
        opacity: 0.6;
        z-index: 0;
        pointer-events: none;
    }

    /* Welcome Header - dark text on light sky blue background for visibility */
    .welcome-header {
        color: #0c4a6e;
    }

    .welcome-header .text-primary,
    .welcome-header h1 {
        color: #0284c7 !important;
    }

    .text-gradient {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Ensure all welcome text is visible on light background */
    .welcome-subtext {
        color: #334155 !important;
    }

    .welcome-optional-hint {
        color: #475569 !important;
        font-weight: 500;
    }

    .welcome-progress-text {
        color: #475569 !important;
    }

    .welcome-icon-sky {
        color: #0284c7 !important;
    }

    .selection-badge-sky {
        background: rgba(255, 255, 255, 0.95) !important;
        color: #0284c7 !important;
    }

    .welcome-footer-hint {
        color: #475569 !important;
    }

    .modal-header-sky {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
    }

    .profile-link-sky {
        color: #0284c7 !important;
    }

    .profile-link-sky:hover {
        color: #0369a1 !important;
    }

    /* Progress Indicator */
    .progress-indicator {
        max-width: 500px;
        margin: 0 auto;
    }

    .progress-bar-custom {
        width: 100%;
        height: 8px;
        background: rgba(14, 165, 233, 0.2);
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #0284c7 0%, #0ea5e9 100%);
        border-radius: 10px;
        transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 0 10px rgba(14, 165, 233, 0.4);
    }

    /* Card Header Custom - sky blue */
    .card-header-custom {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        color: white;
        padding: 2rem;
        border: none;
    }

    .card-header-hint {
        color: rgba(255, 255, 255, 0.95) !important;
        opacity: 1;
    }

    .selection-badge {
        animation: pulse 2s ease-in-out infinite;
    }

    /* Category Cards */
    .category-item {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .category-card-wrapper {
        position: relative;
        height: 100%;
    }

    .category-label {
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-radius: 15px;
        overflow: hidden;
        background: white;
        padding: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .category-label:hover {
        transform: translateY(-6px) scale(1.02);
        box-shadow: 0 12px 30px rgba(14, 165, 233, 0.25) !important;
    }

    .category-content {
        padding: 1.75rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .category-name {
        color: #1e293b;
        font-size: 1.2rem;
        transition: color 0.3s ease;
    }

    .category-description {
        line-height: 1.5;
        color: #64748b;
    }

    /* Selection Indicator - sky blue */
    .selection-indicator {
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

    .selection-indicator i {
        color: #0284c7;
        font-size: 1.35rem;
    }

    .category-checkbox:checked + .category-label {
        border: 3px solid #0ea5e9 !important;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(56, 189, 248, 0.08) 100%);
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.25) !important;
    }

    .category-checkbox:checked + .category-label .selection-indicator {
        opacity: 1;
        transform: scale(1);
    }

    .category-checkbox:checked + .category-label .category-name {
        color: #0284c7;
    }

    /* Hover Effect */
    .category-hover-effect {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s ease;
    }

    .category-label:hover .category-hover-effect {
        left: 100%;
    }

    /* Submit Button - sky blue */
    .submit-button {
        position: relative;
        overflow: hidden;
        border-radius: 50px;
        font-weight: 600;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
        border: none !important;
        box-shadow: 0 8px 24px rgba(14, 165, 233, 0.4);
        transition: all 0.3s ease;
        min-width: 200px;
        color: white !important;
    }

    .submit-button:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(14, 165, 233, 0.5);
        background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%) !important;
        color: white !important;
    }

    .submit-button:disabled,
    .submit-button.btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background: #94a3b8 !important;
        background-color: #94a3b8 !important;
        box-shadow: none !important;
        color: white !important;
        border-color: #94a3b8 !important;
    }

    .submit-button:disabled:hover,
    .submit-button.btn-primary:disabled:hover {
        background: #94a3b8 !important;
        background-color: #94a3b8 !important;
        color: white !important;
        transform: none;
    }

    .submit-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.5);
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%) !important;
        color: white !important;
    }

    /* Skip Button - white with sky blue border */
    .btn-skip-bottom {
        border: 2px solid #0ea5e9;
        color: #0284c7 !important;
        background: white !important;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
        min-width: 180px;
    }

    .btn-skip-bottom i,
    .btn-skip-bottom span {
        color: #0284c7 !important;
    }

    .btn-skip-bottom:hover {
        background: #f0f9ff !important;
        border-color: #0284c7;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.25);
        color: #0369a1 !important;
    }

    .btn-skip-bottom:hover i,
    .btn-skip-bottom:hover span {
        color: #0369a1 !important;
    }

    .btn-skip-bottom:focus {
        color: #0284c7 !important;
        background: white !important;
        box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.3);
    }

    .btn-skip-bottom:focus i,
    .btn-skip-bottom:focus span {
        color: #0284c7 !important;
    }

    .btn-skip-bottom:active {
        color: #0369a1 !important;
        background: #e0f2fe !important;
        transform: translateY(-1px);
    }

    .btn-skip-bottom:active i,
    .btn-skip-bottom:active span {
        color: #0369a1 !important;
    }

    /* Modal Enhancements - sky blue */
    .skip-confirm-btn {
        background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .skip-confirm-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.4);
    }

    #skipModal .modal-content {
        animation: scaleIn 0.3s ease-out;
    }

    .submit-button:not(:disabled)::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .submit-button:not(:disabled):hover::before {
        width: 300px;
        height: 300px;
    }

    .button-content,
    .button-loader {
        position: relative;
        z-index: 1;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes animate-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }

    .animate-shake {
        animation: animate-shake 0.5s ease-in-out;
    }

    /* Remove any default margins/padding that might cause white lines */
    .category-selection-wrapper * {
        box-sizing: border-box;
    }

    /* Ensure container doesn't create white spaces */
    .category-selection-wrapper .container {
        margin-left: auto;
        margin-right: auto;
        padding-left: 15px;
        padding-right: 15px;
        max-width: 100%;
        width: 100%;
    }
    
    /* Fix any potential white gaps */
    .category-selection-wrapper .container-fluid {
        padding-left: 0;
        padding-right: 0;
    }

    /* Remove any white space from main content area */
    main {
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        min-height: 100vh;
    }
    
    /* Ensure html and body take full height */
    html.category-selection-html, 
    body.category-selection-body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    
    /* Prevent body scrollbar on category selection page */
    body.category-selection-body {
        position: relative;
    }

    /* Ensure no white background shows through */
    .category-selection-wrapper .row {
        margin-left: 0;
        margin-right: 0;
    }

    /* Remove any gaps between elements */
    .category-selection-wrapper > * {
        margin-left: 0;
        margin-right: 0;
    }
    
    /* Ensure no white space shows at edges */
    .category-selection-wrapper,
    .category-selection-wrapper * {
        box-sizing: border-box;
    }
    
    /* Fix potential Bootstrap container issues */
    .category-selection-wrapper .row {
        margin-left: -15px;
        margin-right: -15px;
    }
    
    .category-selection-wrapper .row > * {
        padding-left: 15px;
        padding-right: 15px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .category-selection-wrapper {
            padding: 1rem 0;
            min-height: 100vh;
        }
        
        body.category-selection-body,
        html.category-selection-html {
            background: linear-gradient(135deg, #e0f4ff 0%, #f0f9ff 50%, #ffffff 100%) !important;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        .welcome-header h1 {
            font-size: 2rem;
        }
        
        .card-header-custom {
            padding: 1.5rem;
        }
        
        .selection-badge {
            display: none;
        }
        
        .category-selection-wrapper .container {
            padding-left: 10px;
            padding-right: 10px;
        }
        
        .submit-button,
        .btn-skip-bottom {
            width: 100%;
            max-width: 100%;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }
        
        .submit-button .button-content span,
        .btn-skip-bottom span {
            display: inline;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        const submitBtn = document.getElementById('submitBtn');
        const selectedCount = document.getElementById('selectedCount');
        const badgeCount = document.getElementById('badgeCount');
        const progressFill = document.getElementById('progressFill');
        const totalCategories = parseInt(document.getElementById('totalCategories').textContent);
        const form = document.getElementById('categoryForm');
        const categoryItems = document.querySelectorAll('.category-item');
        
        // Animate category items on load
        categoryItems.forEach((item, index) => {
            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        function updateUI() {
            const checked = document.querySelectorAll('.category-checkbox:checked').length;
            const percentage = totalCategories > 0 ? (checked / totalCategories) * 100 : 0;
            
            selectedCount.textContent = checked;
            badgeCount.textContent = checked;
            progressFill.style.width = percentage + '%';
            
            if (checked > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
            
            // Add pulse animation to badge when selection changes
            const badge = document.getElementById('selectionBadge');
            if (checked > 0) {
                badge.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => {
                    badge.classList.remove('animate__pulse');
                }, 1000);
            }
        }
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateUI();
                
                // Add selection animation
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
                const csrfInput = form.querySelector('input[name="_token"]');
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                
                if (!csrfInput || !csrfInput.value) {
                    e.preventDefault();
                    window.location.reload();
                    return false;
                }
                
                if (metaToken && csrfInput) {
                    csrfInput.value = metaToken.getAttribute('content');
                }
                
                // Show loading state
                const buttonContent = submitBtn.querySelector('.button-content');
                const buttonLoader = submitBtn.querySelector('.button-loader');
                if (buttonContent && buttonLoader) {
                    buttonContent.classList.add('d-none');
                    buttonLoader.classList.remove('d-none');
                    submitBtn.disabled = true;
                }
            });
        }
        
        // Initial check
        updateUI();
    });
</script>

<!-- Skip Confirmation Modal -->
<div class="modal fade" id="skipModal" tabindex="-1" aria-labelledby="skipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 modal-header-sky" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title text-white fw-bold" id="skipModalLabel">
                    <i class="bi bi-question-circle me-2"></i>Skip Category Selection?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-info-circle text-primary" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-3 fw-semibold">
                    Skip category selection?
                </p>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-gear me-2"></i>
                    <strong>No problem!</strong> This step is completely optional. You can set up your category preferences anytime later in your <a href="{{ route('profile.edit') }}" class="alert-link fw-bold">Profile Settings</a>.
                </div>
                <p class="text-muted small text-center mb-0">
                    <i class="bi bi-lightbulb me-1"></i>
                    Selecting categories helps us show you personalized product recommendations, but you can always browse all products without them!
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancel
                </button>
                <form method="POST" action="{{ route('category-preferences.skip') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary px-4 skip-confirm-btn">
                        <i class="bi bi-check-circle me-2"></i>Skip & Continue to Homepage
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush
@endsection
