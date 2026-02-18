<x-guest-layout>
    <h3 class="text-center mb-4 fw-bold reveal">Reset Password</h3>
    <p class="text-center text-muted mb-4 reveal animate-delay-1">Enter your new password below</p>

    <form method="POST" action="{{ route('password.store') }}" class="reveal animate-delay-2" id="resetPasswordForm">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <!-- Email Address (hidden, retrieved from query string or request) -->
        <input type="hidden" name="email" value="{{ old('email', $request->query('email', $request->email)) }}">

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">
                <i class="bi bi-lock me-2"></i>New Password
            </label>
            <div class="position-relative">
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    required 
                    autofocus 
                    autocomplete="new-password"
                    placeholder="Enter new password"
                    style="padding-right: 45px;">
                <button 
                    type="button" 
                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 password-toggle-btn" 
                    onclick="togglePassword('password')" 
                    style="text-decoration: none; border: none; background: none; z-index: 10; padding: 0.375rem 0.75rem;"
                    aria-label="Toggle password visibility">
                    <i class="bi bi-eye" id="password-toggle"></i>
                </button>
            </div>
            <!-- Password Strength Meter -->
            <div class="mt-2">
                <div class="progress" style="height: 5px;">
                    <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%; transition: all 0.3s ease;"></div>
                </div>
                <small id="password-strength-text" class="text-muted"></small>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-semibold">
                <i class="bi bi-lock-fill me-2"></i>Confirm New Password
            </label>
            <div class="position-relative">
                <input 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    class="form-control" 
                    required 
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                    style="padding-right: 45px;">
                <button 
                    type="button" 
                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 password-toggle-btn" 
                    onclick="togglePassword('password_confirmation')" 
                    style="text-decoration: none; border: none; background: none; z-index: 10; padding: 0.375rem 0.75rem;"
                    aria-label="Toggle password visibility">
                    <i class="bi bi-eye" id="password_confirmation-toggle"></i>
                </button>
            </div>
            <!-- Password Match Indicator -->
            <div class="mt-2">
                <small id="password-match-text" class="text-muted"></small>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2" id="submitBtn">
                <i class="bi bi-key me-2"></i>Reset Password
            </button>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary py-2">
                <i class="bi bi-x-circle me-2"></i>Cancel
            </a>
        </div>
    </form>

    <style>
        .progress-bar.weak { background-color: #dc3545; }
        .progress-bar.fair { background-color: #ffc107; }
        .progress-bar.good { background-color: #17a2b8; }
        .progress-bar.strong { background-color: #28a745; }
        
        .password-toggle-btn {
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s ease;
        }
        
        .password-toggle-btn:hover {
            color: #495057;
        }
        
        .password-toggle-btn:focus {
            outline: none;
            box-shadow: none;
        }
        
        .password-toggle-btn i {
            font-size: 1.1rem;
        }
        
        .position-relative input[type="password"],
        .position-relative input[type="text"] {
            padding-right: 45px !important;
        }
    </style>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(inputId + '-toggle');
            if (input.type === 'password') {
                input.type = 'text';
                toggle.classList.remove('bi-eye');
                toggle.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                toggle.classList.remove('bi-eye-slash');
                toggle.classList.add('bi-eye');
            }
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = [];

            if (password.length >= 8) strength++;
            else feedback.push('at least 8 characters');

            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            else feedback.push('uppercase and lowercase letters');

            if (/[0-9]/.test(password)) strength++;
            else feedback.push('numbers');

            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            else feedback.push('special characters');

            return { strength, feedback };
        }

        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            const submitBtn = document.getElementById('submitBtn');

            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'progress-bar';
                strengthText.textContent = '';
                return;
            }

            const { strength, feedback } = checkPasswordStrength(password);
            let width = (strength / 4) * 100;
            let className = 'progress-bar';
            let text = '';

            if (strength === 0 || strength === 1) {
                className += ' weak';
                text = 'Weak password. Add: ' + feedback.join(', ');
            } else if (strength === 2) {
                className += ' fair';
                text = 'Fair password. Add: ' + feedback.join(', ');
            } else if (strength === 3) {
                className += ' good';
                text = 'Good password. Add: ' + feedback.join(', ');
            } else {
                className += ' strong';
                text = 'Strong password!';
            }

            strengthBar.style.width = width + '%';
            strengthBar.className = className;
            strengthText.textContent = text;
            strengthText.className = strength === 4 ? 'text-success' : 'text-muted';
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            const matchText = document.getElementById('password-match-text');
            const confirmInput = document.getElementById('password_confirmation');

            if (passwordConfirmation.length === 0) {
                matchText.textContent = '';
                confirmInput.classList.remove('is-invalid', 'is-valid');
                return;
            }

            if (password === passwordConfirmation) {
                matchText.textContent = '✓ Passwords match';
                matchText.className = 'text-success';
                confirmInput.classList.remove('is-invalid');
                confirmInput.classList.add('is-valid');
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.className = 'text-danger';
                confirmInput.classList.remove('is-valid');
                confirmInput.classList.add('is-invalid');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const form = document.getElementById('resetPasswordForm');

            passwordInput.addEventListener('input', updatePasswordStrength);
            passwordConfirmationInput.addEventListener('input', checkPasswordMatch);
            passwordInput.addEventListener('input', checkPasswordMatch);

            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const passwordConfirmation = passwordConfirmationInput.value;

                if (password !== passwordConfirmation) {
                    e.preventDefault();
                    alert('Passwords do not match. Please try again.');
                    return false;
                }

                const { strength } = checkPasswordStrength(password);
                if (strength < 2) {
                    e.preventDefault();
                    alert('Password is too weak. Please choose a stronger password.');
                    return false;
                }
            });
        });
    </script>
</x-guest-layout>
