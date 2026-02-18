<x-guest-layout>
    @push('styles')
    <style>
        /* Wider container for business registration - ToyStore style */
        .auth-container {
            align-items: flex-start !important;
            padding: 2rem 0;
        }
        .auth-container .container {
            max-width: 100%;
        }
        .auth-container .container .row {
            max-width: 100%;
        }
        .auth-container .container .row > div {
            max-width: 900px;
            margin: 0 auto;
            flex: 0 0 100%;
            width: 100%;
        }
        @media (min-width: 768px) {
            .auth-container .container .row > div {
                flex: 0 0 90%;
                max-width: 90%;
            }
        }
        @media (min-width: 992px) {
            .auth-container .container .row > div {
                flex: 0 0 85%;
                max-width: 85%;
            }
        }
        .auth-card {
            overflow: visible !important;
            max-height: none !important;
        }
        .form-section {
            background: #fefcf8;
            border-radius: 16px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.5rem;
            border: 2px solid #f0e6dc;
            border-left: 5px solid #ff6b6b;
        }
        .form-section h5 {
            color: #ff6b6b;
            margin-bottom: 1rem;
            font-weight: 800;
        }
        .auth-card .form-section .form-control:focus {
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
        }
        .auth-card .form-section .form-label {
            color: #2d2a26;
            font-weight: 700;
        }
    </style>
    @endpush

    <!-- Error Status -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show reveal" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h3 class="text-center mb-3 fw-bold reveal">Register Business Account</h3>
    <p class="text-center text-muted mb-4 reveal animate-delay-1">
        @if(($type ?? request('type')) === 'verified')
            Create a verified trusted shop account
        @else
            Start selling on ToyHaven today!
        @endif
    </p>

    @if(($type ?? request('type')) === 'verified')
        <div class="alert alert-success mb-4 reveal animate-delay-1">
            <h6 class="alert-heading"><i class="bi bi-star-fill me-2"></i>Full Verified Trusted Shop Benefits:</h6>
            <ul class="mb-0 small">
                <li>Verified badge on your shop profile</li>
                <li>Priority customer support</li>
                <li>Enhanced trust and credibility</li>
                <li>Featured placement in search results</li>
                <li>Access to advanced analytics</li>
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('business.register') }}" enctype="multipart/form-data" class="reveal animate-delay-2">
        @csrf
        <input type="hidden" name="registration_type" value="{{ $type ?? request('type', 'basic') }}">

        <!-- Account Information Section -->
        <div class="form-section">
            <h5><i class="bi bi-person me-2"></i>Account Information</h5>
            
            <div class="row">
                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input 
                        id="name" 
                        type="text" 
                        name="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}" 
                        required 
                        autofocus 
                        autocomplete="name"
                        placeholder="Enter your full name"
                        pattern="[A-Za-z\s]+"
                        oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Letters and spaces only</small>
                </div>

                <!-- Email Address -->
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-semibold">
                        <i class="bi bi-envelope me-2"></i>Email Address <span class="text-danger">*</span>
                    </label>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        required 
                        autocomplete="username"
                        placeholder="Enter your email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="email-check-feedback" class="mt-1"></div>
                </div>
            </div>

            <div class="row">
                <!-- Password -->
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-lock me-2"></i>Password <span class="text-danger">*</span>
                    </label>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        required 
                        autocomplete="new-password"
                        placeholder="Create a password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="mt-2">
                        <div class="password-strength-meter mb-2">
                            <div class="strength-bar-container" style="height: 5px; background-color: #e9ecef; border-radius: 3px; overflow: hidden;">
                                <div id="strength-bar" class="strength-bar" style="height: 100%; width: 0%; transition: all 0.3s ease; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <div id="password-strength-text" class="small"></div>
                        <div id="password-requirements" class="small mt-2">
                            <div class="text-muted">Password must contain:</div>
                            <ul class="list-unstyled mb-0 ms-3">
                                <li id="req-length" class="text-muted"><i class="bi bi-circle me-1"></i>At least 8 characters</li>
                                <li id="req-uppercase" class="text-muted"><i class="bi bi-circle me-1"></i>One uppercase letter</li>
                                <li id="req-lowercase" class="text-muted"><i class="bi bi-circle me-1"></i>One lowercase letter</li>
                                <li id="req-number" class="text-muted"><i class="bi bi-circle me-1"></i>One number</li>
                                <li id="req-special" class="text-muted"><i class="bi bi-circle me-1"></i>One special character</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        <i class="bi bi-lock-fill me-2"></i>Confirm Password <span class="text-danger">*</span>
                    </label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        name="password_confirmation" 
                        class="form-control @error('password_confirmation') is-invalid @enderror" 
                        required 
                        autocomplete="new-password"
                        placeholder="Confirm your password">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="password-match-feedback" class="mt-1"></div>
                </div>
            </div>
        </div>

        <!-- Business Information Section -->
        <div class="form-section">
            <h5><i class="bi bi-shop me-2"></i>Business Information</h5>
            
            <div class="mb-3">
                <label for="business_name" class="form-label fw-semibold">Business Name <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="business_name" 
                    id="business_name"
                    class="form-control @error('business_name') is-invalid @enderror" 
                    value="{{ old('business_name') }}" 
                    required
                    placeholder="Enter your business name">
                @error('business_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Business Description</label>
                <textarea 
                    name="description" 
                    id="description"
                    class="form-control @error('description') is-invalid @enderror" 
                    rows="3"
                    placeholder="Describe your business">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone_display" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone-fill me-1"></i>+63</span>
                        <input 
                            type="tel" 
                            id="phone_display"
                            class="form-control @error('phone') is-invalid @enderror" 
                            value="{{ old('phone') ? (preg_match('/^\+63(\d{10})$/', old('phone'), $m) ? $m[1] : preg_replace('/\D/', '', old('phone'))) : '' }}" 
                            placeholder="9123456789"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            inputmode="numeric"
                            autocomplete="tel"
                            title="10-digit Philippine mobile number">
                    </div>
                    <input type="hidden" name="phone" id="phone" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Philippines only. 10 digits (e.g. 9123456789).</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="business_email" class="form-label fw-semibold">
                        Business Email <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input 
                            type="email" 
                            name="business_email" 
                            id="business_email"
                            class="form-control @error('business_email') is-invalid @enderror" 
                            value="{{ old('business_email') }}" 
                            required
                            placeholder="Enter business email">
                        <button type="button" class="btn btn-outline-primary" id="usePersonalEmailBtn" title="Use Personal Email">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                    </div>
                    @error('business_email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div id="business-email-check-feedback" class="mt-1"></div>
                </div>
            </div>
        </div>

        <!-- Business Address Section -->
        <div class="form-section">
            <h5><i class="bi bi-geo-alt me-2"></i>Business Address</h5>
            
            <div class="mb-3">
                <label for="address" class="form-label fw-semibold">Full Address <span class="text-danger">*</span></label>
                <textarea 
                    name="address" 
                    id="address"
                    class="form-control @error('address') is-invalid @enderror" 
                    rows="2" 
                    required
                    placeholder="Enter full address">{{ old('address') }}</textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="city" class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="city" 
                        id="city"
                        class="form-control @error('city') is-invalid @enderror" 
                        value="{{ old('city') }}" 
                        required
                        placeholder="City">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="province" class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="province" 
                        id="province"
                        class="form-control @error('province') is-invalid @enderror" 
                        value="{{ old('province') }}" 
                        required
                        placeholder="Province">
                    @error('province')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="postal_code" class="form-label fw-semibold">Postal Code <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="postal_code" 
                        id="postal_code"
                        class="form-control @error('postal_code') is-invalid @enderror" 
                        value="{{ old('postal_code') }}" 
                        required
                        placeholder="Postal Code">
                    @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Required Documents Section -->
        <div class="form-section">
            <h5><i class="bi bi-file-earmark me-2"></i>Required Documents</h5>
            <p class="text-muted small mb-3">Please upload the following documents for verification:</p>

            @if(($type ?? request('type')) === 'verified')
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>For full verified trusted shop status, additional documents are required.
                </div>

                <div class="mb-3">
                    <label for="business_registration" class="form-label fw-semibold">Business Registration <span class="text-danger">*</span></label>
                    <input 
                        type="file" 
                        name="business_registration" 
                        id="business_registration"
                        class="form-control @error('business_registration') is-invalid @enderror" 
                        accept=".pdf,.jpg,.jpeg,.png" 
                        required>
                    <small class="text-muted d-block">BIR Form 2303 (COR) + DTI/SEC Permit. Upload the original PDF or a high-res scan. Do not upload a screenshot.</small>
                    <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                    @error('business_registration')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="brand_rights" class="form-label fw-semibold">Brand Rights <span class="text-danger">*</span></label>
                    <input 
                        type="file" 
                        name="brand_rights" 
                        id="brand_rights"
                        class="form-control @error('brand_rights') is-invalid @enderror" 
                        accept=".pdf,.jpg,.jpeg,.png" 
                        required>
                    <small class="text-muted d-block">Trademark Certificate OR Letter of Authorization (LOA). If you are a distributor, the LOA must be on the manufacturer's letterhead, signed, and dated within the last 1 year.</small>
                    <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                    @error('brand_rights')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="mb-3">
                <label for="id_document" class="form-label fw-semibold">Primary ID <span class="text-danger">*</span></label>
                <input 
                    type="file" 
                    name="id_document" 
                    id="id_document"
                    class="form-control @error('id_document') is-invalid @enderror" 
                    accept=".pdf,.jpg,.jpeg,.png" 
                    required>
                <small class="text-muted">Passport, Driver's License, UMID, or National ID. PDF, JPG, PNG (Max: 5MB)</small>
                @error('id_document')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="bank_document" class="form-label fw-semibold">Bank Document <span class="text-danger">*</span></label>
                <input 
                    type="file" 
                    name="bank_document" 
                    id="bank_document"
                    class="form-control @error('bank_document') is-invalid @enderror" 
                    accept=".pdf,.jpg,.jpeg,.png" 
                    required>
                <small class="text-muted d-block">Bank statement or Passbook photo.</small>
                <small class="text-warning d-block"><strong>Crucial:</strong> The name on the ID must match the bank account name exactly. (e.g., if ID says "Maria A. Cruz", Bank cannot be "Maria Cruz").</small>
                <small class="text-muted">PDF, JPG, PNG (Max: 5MB)</small>
                @error('bank_document')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 {{ $errors->has('toy_category_ids') ? 'is-invalid' : '' }}">
                <label class="form-label fw-semibold">Toy Category You Sell <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-2" id="toy-category-buttons">
                    @foreach($categories ?? [] as $cat)
                        @php $oldIds = old('toy_category_ids', []); $isOld = is_array($oldIds) && in_array($cat->id, $oldIds); @endphp
                        <input type="checkbox" class="btn-check" name="toy_category_ids[]" value="{{ $cat->id }}" id="toy_cat_{{ $cat->id }}" {{ $isOld ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary text-start d-block mb-0 py-2 px-3" for="toy_cat_{{ $cat->id }}" style="min-width: 200px; max-width: 280px;">
                            <span class="fw-semibold d-block">{{ $cat->name }}</span>
                            @if(!empty($cat->description))
                                <small class="text-muted d-block mt-1 lh-sm">{{ $cat->description }}</small>
                            @endif
                        </label>
                    @endforeach
                </div>
                <small class="text-muted d-block mt-2">Select one or more toy categories you sell. Click to toggle.</small>
                @error('toy_category_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i>Your documents will be reviewed by our admin team. You will be notified once your account is verified.
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-lg py-2">
                @if(($type ?? request('type')) === 'verified')
                    <i class="bi bi-shield-check me-2"></i>Submit Verified Shop Registration
                @else
                    <i class="bi bi-person-plus me-2"></i>Create Business Account
                @endif
            </button>
        </div>

        <!-- Login Link -->
        <div class="text-center mt-3">
            <p class="text-muted mb-2">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign in</a>
            </p>
            <p class="text-muted mb-0">
                Want to register as a customer? 
                <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Customer Registration</a>
            </p>
        </div>
    </form>

    <script>
        // Philippines phone: 10 digits, store as +63xxxxxxxxxx
        (function() {
            var phoneDisplay = document.getElementById('phone_display');
            var phoneHidden = document.getElementById('phone');
            if (phoneDisplay && phoneHidden) {
                phoneDisplay.addEventListener('input', function() {
                    var digits = this.value.replace(/\D/g, '').slice(0, 10);
                    this.value = digits;
                    phoneHidden.value = digits.length === 10 ? '+63' + digits : '';
                });
                phoneDisplay.addEventListener('keypress', function(e) {
                    if (e.key && !/\d/.test(e.key) && !e.ctrlKey && !e.metaKey && e.key.length === 1) e.preventDefault();
                });
                if (phoneDisplay.value) {
                    var d = phoneDisplay.value.replace(/\D/g, '').slice(0, 10);
                    phoneDisplay.value = d;
                    if (d.length === 10) phoneHidden.value = '+63' + d;
                }
            }
        })();

        // Password strength checker (same as register.blade.php)
        function checkPasswordStrength(password) {
            let strength = 0;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            if (requirements.length) strength++;
            if (requirements.uppercase) strength++;
            if (requirements.lowercase) strength++;
            if (requirements.number) strength++;
            if (requirements.special) strength++;

            updateRequirement('req-length', requirements.length);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-lowercase', requirements.lowercase);
            updateRequirement('req-number', requirements.number);
            updateRequirement('req-special', requirements.special);

            let strengthLevel = 'weak';
            let strengthText = 'Weak';
            let strengthColor = '#dc3545';
            let strengthWidth = '20%';

            if (strength <= 2) {
                strengthLevel = 'weak';
                strengthText = 'Weak';
                strengthColor = '#dc3545';
                strengthWidth = '20%';
            } else if (strength === 3) {
                strengthLevel = 'medium';
                strengthText = 'Medium';
                strengthColor = '#ffc107';
                strengthWidth = '60%';
            } else if (strength >= 4) {
                strengthLevel = 'strong';
                strengthText = 'Strong';
                strengthColor = '#28a745';
                strengthWidth = '100%';
            }

            const strengthBar = document.getElementById('strength-bar');
            const strengthTextEl = document.getElementById('password-strength-text');
            
            if (password.length > 0) {
                strengthBar.style.width = strengthWidth;
                strengthBar.style.backgroundColor = strengthColor;
                strengthTextEl.innerHTML = `<span class="fw-semibold" style="color: ${strengthColor};">Password Strength: ${strengthText}</span>`;
            } else {
                strengthBar.style.width = '0%';
                strengthTextEl.innerHTML = '';
            }

            return { strengthLevel, requirements, strength };
        }

        function updateRequirement(id, met) {
            const el = document.getElementById(id);
            if (met) {
                el.innerHTML = el.innerHTML.replace('bi-circle', 'bi-check-circle-fill');
                el.classList.remove('text-muted');
                el.classList.add('text-success');
            } else {
                el.innerHTML = el.innerHTML.replace('bi-check-circle-fill', 'bi-circle');
                el.classList.remove('text-success');
                el.classList.add('text-muted');
            }
        }

        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const passwordMatchFeedback = document.getElementById('password-match-feedback');
        let passwordStrength = null;
        
        password.addEventListener('input', function() {
            const pass = this.value;
            passwordStrength = checkPasswordStrength(pass);
            
            if (passwordConfirmation.value) {
                checkPasswordMatch();
            }
        });
        
        function checkPasswordMatch() {
            const pass = password.value;
            const confirm = passwordConfirmation.value;
            
            if (confirm === '') {
                passwordMatchFeedback.innerHTML = '';
                passwordConfirmation.classList.remove('is-invalid', 'is-valid');
                return;
            }
            
            if (pass === confirm) {
                passwordMatchFeedback.innerHTML = '<small class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</small>';
                passwordConfirmation.classList.remove('is-invalid');
                passwordConfirmation.classList.add('is-valid');
            } else {
                passwordMatchFeedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</small>';
                passwordConfirmation.classList.remove('is-valid');
                passwordConfirmation.classList.add('is-invalid');
            }
        }
        
        passwordConfirmation.addEventListener('input', checkPasswordMatch);
        
        document.querySelector('form').addEventListener('submit', function(e) {
            var phoneDisplay = document.getElementById('phone_display');
            var phoneHidden = document.getElementById('phone');
            if (phoneDisplay && phoneHidden) {
                var digits = phoneDisplay.value.replace(/\D/g, '');
                if (digits.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit Philippine phone number.');
                    phoneDisplay.focus();
                    return false;
                }
                phoneHidden.value = '+63' + digits;
            }
            const pass = password.value;
            const confirm = passwordConfirmation.value;
            passwordStrength = checkPasswordStrength(pass);
            
            if (passwordStrength.strengthLevel === 'weak') {
                e.preventDefault();
                alert('Please use a medium to strong password. Your password is currently weak.');
                password.focus();
                return false;
            }
            
            if (pass !== confirm) {
                e.preventDefault();
                alert('Passwords do not match. Please enter the same password in both fields.');
                passwordConfirmation.focus();
                return false;
            }
        });

        // Real-time email checking for personal email
        const emailInput = document.getElementById('email');
        const emailFeedback = document.getElementById('email-check-feedback');
        
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim().toLowerCase();
                
                if (email === '') {
                    emailFeedback.innerHTML = '';
                    return;
                }
                
                // Simple email format check
                if (!email.includes('@')) {
                    return;
                }
                
                fetch('{{ route('business.check-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        email: email,
                        type: 'personal'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        emailFeedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle me-1"></i>' + (data.message || 'This email is already registered.') + '</small>';
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    } else {
                        emailFeedback.innerHTML = '<small class="text-success"><i class="bi bi-check-circle me-1"></i>' + (data.message || 'Email is available.') + '</small>';
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }

        // Business email field (must be defined before "Use personal email" button handler)
        const businessEmailInput = document.getElementById('business_email');
        const businessEmailFeedback = document.getElementById('business-email-check-feedback');

        // Use personal email for business email – copy Account Information email into Business Email
        const usePersonalEmailBtn = document.getElementById('usePersonalEmailBtn');
        if (usePersonalEmailBtn && emailInput && businessEmailInput) {
            usePersonalEmailBtn.addEventListener('click', function() {
                const personalEmail = emailInput.value.trim();
                if (personalEmail && personalEmail.includes('@')) {
                    businessEmailInput.value = personalEmail;
                    businessEmailInput.dispatchEvent(new Event('blur'));
                    this.innerHTML = '<i class="bi bi-check-circle me-1"></i> Copied';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-arrow-left-right"></i>';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-primary');
                    }, 2000);
                } else {
                    alert('Please enter a valid email in Account Information first.');
                    emailInput.focus();
                }
            });
        }

        // Real-time email checking for business email
        if (businessEmailInput) {
            businessEmailInput.addEventListener('blur', function() {
                const email = this.value.trim().toLowerCase();
                
                if (email === '') {
                    businessEmailFeedback.innerHTML = '';
                    return;
                }
                
                // Simple email format check
                if (!email.includes('@')) {
                    return;
                }
                
                fetch('{{ route('business.check-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        email: email,
                        type: 'business'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        businessEmailFeedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle me-1"></i>' + (data.message || 'This business email is already in use.') + '</small>';
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    } else {
                        businessEmailFeedback.innerHTML = '<small class="text-success"><i class="bi bi-check-circle me-1"></i>' + (data.message || 'Business email is available.') + '</small>';
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
    </script>
</x-guest-layout>
