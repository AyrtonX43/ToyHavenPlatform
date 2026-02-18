<x-guest-layout>
    <!-- Error Status -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show reveal" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h3 class="text-center mb-4 fw-bold reveal">Create Account</h3>
    <p class="text-center text-muted mb-4 reveal animate-delay-1">Join ToyHaven and start shopping today!</p>

    <form method="POST" action="{{ route('register') }}" class="reveal animate-delay-2">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">
                <i class="bi bi-person me-2"></i>Full Name
            </label>
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
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="form-text">Letters and spaces only</small>
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">
                <i class="bi bi-envelope me-2"></i>Email Address
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
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div id="email-check-feedback" class="form-feedback"></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">
                <i class="bi bi-lock me-2"></i>Password
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
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            
            <!-- Password Strength Meter -->
            <div class="password-strength-meter">
                <div class="strength-bar-container mb-2" style="height: 5px; background-color: #e9ecef; border-radius: 3px; overflow: hidden;">
                    <div id="strength-bar" class="strength-bar" style="height: 100%; width: 0%; transition: all 0.3s ease; border-radius: 3px;"></div>
                </div>
                <div id="password-strength-text" class="small mb-2"></div>
                <div id="password-requirements" class="password-requirements">
                    <div class="text-muted small mb-2">Password must contain:</div>
                    <ul class="list-unstyled mb-0">
                        <li id="req-length" class="text-muted small"><i class="bi bi-circle"></i><span class="ms-2">At least 8 characters</span></li>
                        <li id="req-uppercase" class="text-muted small"><i class="bi bi-circle"></i><span class="ms-2">One uppercase letter</span></li>
                        <li id="req-lowercase" class="text-muted small"><i class="bi bi-circle"></i><span class="ms-2">One lowercase letter</span></li>
                        <li id="req-number" class="text-muted small"><i class="bi bi-circle"></i><span class="ms-2">One number</span></li>
                        <li id="req-special" class="text-muted small"><i class="bi bi-circle"></i><span class="ms-2">One special character</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">
                <i class="bi bi-lock-fill me-2"></i>Confirm Password
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
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div id="password-match-feedback" class="form-feedback"></div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
            <i class="bi bi-person-plus me-2"></i>Create Account
        </button>

        <!-- Divider -->
        <div class="position-relative my-4">
            <hr>
            <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">
                Or continue with
            </div>
        </div>

        <!-- Google Register Button -->
        <a href="{{ route('google.auth') }}?from=register" class="btn btn-outline-secondary w-100 py-2">
            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <!-- Login Link -->
        <div class="text-center mt-4">
            <p class="text-muted mb-0">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Sign in</a>
            </p>
            <p class="text-muted mb-0 mt-2">
                Want to register as a business? 
                <a href="{{ route('business.register') }}" class="text-primary fw-semibold text-decoration-none">Business Registration</a>
            </p>
        </div>
    </form>

    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };

            // Calculate strength score
            if (requirements.length) strength++;
            if (requirements.uppercase) strength++;
            if (requirements.lowercase) strength++;
            if (requirements.number) strength++;
            if (requirements.special) strength++;

            // Update requirement indicators
            updateRequirement('req-length', requirements.length);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-lowercase', requirements.lowercase);
            updateRequirement('req-number', requirements.number);
            updateRequirement('req-special', requirements.special);

            // Determine strength level
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

            // Update UI
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
            const icon = el.querySelector('i');
            const text = el.querySelector('span');
            
            if (met) {
                icon.className = 'bi bi-check-circle-fill';
                el.classList.remove('text-muted');
                el.classList.add('text-success');
            } else {
                icon.className = 'bi bi-circle';
                el.classList.remove('text-success');
                el.classList.add('text-muted');
            }
        }

        // Real-time password strength checking
        const password = document.getElementById('password');
        const passwordConfirmation = document.getElementById('password_confirmation');
        const passwordMatchFeedback = document.getElementById('password-match-feedback');
        let passwordStrength = null;
        
        password.addEventListener('input', function() {
            const pass = this.value;
            passwordStrength = checkPasswordStrength(pass);
            
            // Re-check match if confirm password has value
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
                passwordMatchFeedback.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i><span>Passwords match</span></small>';
                passwordConfirmation.classList.remove('is-invalid');
                passwordConfirmation.classList.add('is-valid');
            } else {
                passwordMatchFeedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i><span>Passwords do not match. Please enter the same password.</span></small>';
                passwordConfirmation.classList.remove('is-valid');
                passwordConfirmation.classList.add('is-invalid');
            }
        }
        
        passwordConfirmation.addEventListener('input', checkPasswordMatch);
        
        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const pass = password.value;
            const confirm = passwordConfirmation.value;
            passwordStrength = checkPasswordStrength(pass);
            
            // Check password strength
            if (passwordStrength.strengthLevel === 'weak') {
                e.preventDefault();
                alert('Please use a medium to strong password. Your password is currently weak.');
                password.focus();
                return false;
            }
            
            // Check password match
            if (pass !== confirm) {
                e.preventDefault();
                alert('Passwords do not match. Please enter the same password in both fields.');
                passwordConfirmation.focus();
                return false;
            }
        });
        
        // Real-time email existence check on register
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            const feedbackDiv = document.getElementById('email-check-feedback');
            
            if (email === '') {
                feedbackDiv.innerHTML = '';
                return;
            }
            
            // Simple email format check
            if (!email.includes('@')) {
                return;
            }
            
            fetch('{{ route('auth.check-email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    feedbackDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i><span>This email is already registered. Please sign in instead.</span></small>';
                    this.classList.add('is-invalid');
                } else {
                    feedbackDiv.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i><span>Email is available</span></small>';
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</x-guest-layout>
