<section class="mb-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Update Password') }}</h5>
            <p class="text-muted small mb-0 mt-2">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

                <div class="mb-3">
                    <label for="update_password_current_password" class="form-label">{{ __('Current Password') }}</label>
                    <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('updatePassword.current_password') is-invalid @enderror" autocomplete="current-password">
                    @error('updatePassword.current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
        </div>

                <div class="mb-3">
                    <label for="update_password_password" class="form-label">{{ __('New Password') }}</label>
                    <input id="update_password_password" name="password" type="password" class="form-control @error('updatePassword.password') is-invalid @enderror" autocomplete="new-password">
                    @error('updatePassword.password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="mt-2">
                        <div class="password-strength-meter mb-2">
                            <div class="strength-bar-container" style="height: 5px; background-color: #e9ecef; border-radius: 3px; overflow: hidden;">
                                <div id="update-strength-bar" class="strength-bar" style="height: 100%; width: 0%; transition: all 0.3s ease; border-radius: 3px;"></div>
                            </div>
                        </div>
                        <div id="update-password-strength-text" class="small"></div>
                        <div id="update-password-requirements" class="small mt-2">
                            <div class="text-muted">Password must contain:</div>
                            <ul class="list-unstyled mb-0 ms-3">
                                <li id="update-req-length" class="text-muted"><i class="bi bi-circle me-1"></i>At least 8 characters</li>
                                <li id="update-req-uppercase" class="text-muted"><i class="bi bi-circle me-1"></i>One uppercase letter</li>
                                <li id="update-req-lowercase" class="text-muted"><i class="bi bi-circle me-1"></i>One lowercase letter</li>
                                <li id="update-req-number" class="text-muted"><i class="bi bi-circle me-1"></i>One number</li>
                                <li id="update-req-special" class="text-muted"><i class="bi bi-circle me-1"></i>One special character</li>
                            </ul>
                        </div>
                    </div>
        </div>

                <div class="mb-3">
                    <label for="update_password_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('updatePassword.password_confirmation') is-invalid @enderror" autocomplete="new-password">
                    @error('updatePassword.password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="update-password-match-feedback" class="mt-1"></div>
        </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary" id="updatePasswordBtn">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                        <p class="text-success mb-0">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
        </div>
    </div>
</section>

<script>
    // Password strength checker for password update form
    function checkUpdatePasswordStrength(password) {
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

        updateUpdateRequirement('update-req-length', requirements.length);
        updateUpdateRequirement('update-req-uppercase', requirements.uppercase);
        updateUpdateRequirement('update-req-lowercase', requirements.lowercase);
        updateUpdateRequirement('update-req-number', requirements.number);
        updateUpdateRequirement('update-req-special', requirements.special);

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

        const strengthBar = document.getElementById('update-strength-bar');
        const strengthTextEl = document.getElementById('update-password-strength-text');
        
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

    function updateUpdateRequirement(id, met) {
        const el = document.getElementById(id);
        if (!el) return;
        
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

    const updatePassword = document.getElementById('update_password_password');
    const updatePasswordConfirmation = document.getElementById('update_password_password_confirmation');
    const updatePasswordMatchFeedback = document.getElementById('update-password-match-feedback');
    let updatePasswordStrength = null;
    
    if (updatePassword) {
        updatePassword.addEventListener('input', function() {
            const pass = this.value;
            updatePasswordStrength = checkUpdatePasswordStrength(pass);
            
            if (updatePasswordConfirmation && updatePasswordConfirmation.value) {
                checkUpdatePasswordMatch();
            }
        });
    }
    
    function checkUpdatePasswordMatch() {
        const pass = updatePassword.value;
        const confirm = updatePasswordConfirmation.value;
        
        if (confirm === '') {
            updatePasswordMatchFeedback.innerHTML = '';
            updatePasswordConfirmation.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        if (pass === confirm) {
            updatePasswordMatchFeedback.innerHTML = '<small class="text-success"><i class="bi bi-check-circle me-1"></i>Passwords match</small>';
            updatePasswordConfirmation.classList.remove('is-invalid');
            updatePasswordConfirmation.classList.add('is-valid');
        } else {
            updatePasswordMatchFeedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle me-1"></i>Passwords do not match</small>';
            updatePasswordConfirmation.classList.remove('is-valid');
            updatePasswordConfirmation.classList.add('is-invalid');
        }
    }
    
    if (updatePasswordConfirmation) {
        updatePasswordConfirmation.addEventListener('input', checkUpdatePasswordMatch);
    }
    
    const updatePasswordForm = document.querySelector('form[action="{{ route("password.update") }}"]');
    if (updatePasswordForm) {
        updatePasswordForm.addEventListener('submit', function(e) {
            const pass = updatePassword.value;
            const confirm = updatePasswordConfirmation.value;
            updatePasswordStrength = checkUpdatePasswordStrength(pass);
            
            if (updatePasswordStrength && updatePasswordStrength.strengthLevel === 'weak') {
                e.preventDefault();
                alert('Please use a medium to strong password. Your password is currently weak.');
                updatePassword.focus();
                return false;
            }
            
            if (pass !== confirm) {
                e.preventDefault();
                alert('Passwords do not match. Please enter the same password in both fields.');
                updatePasswordConfirmation.focus();
                return false;
            }
        });
    }
</script>
