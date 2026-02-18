<section class="mb-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Profile Information') }}</h5>
            <p class="text-muted small mb-0 mt-2">{{ __("Update your account's profile information and email address.") }}</p>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <div class="input-group">
                        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @if ($user->hasVerifiedEmail())
                            <span class="input-group-text bg-success text-white">
                                <i class="bi bi-check-circle me-1"></i>Verified
                            </span>
                        @else
                            <span class="input-group-text bg-warning text-dark">
                                <i class="bi bi-exclamation-triangle me-1"></i>Unverified
                            </span>
                        @endif
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div id="email-check-feedback" class="mt-1"></div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success mt-2" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Email sent.</strong> We've sent a verification email to <strong>{{ $user->email }}</strong>. Please check your inbox and spam folder, then click the link in the email to verify your address.
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger mt-2" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="alert alert-warning mt-2">
                            <p class="mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ __('Your email address is unverified.') }}
                            </p>
                            <div id="verify-email-error" class="text-danger small mb-2 d-none"></div>
                            <button type="button" class="btn btn-sm btn-outline-warning" id="verify-email-btn">
                                <i class="bi bi-envelope-paper me-1"></i><span id="verify-email-btn-text">{{ __('Verify Email Address') }}</span>
                                <span class="spinner-border spinner-border-sm d-none ms-1" id="verify-email-spinner" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    @elseif ($user->hasVerifiedEmail())
                        <div class="alert alert-info mt-2">
                            <p class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>{{ __('Your email address is verified.') }}
                            </p>
                        </div>
                    @endif
                    
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-info-circle me-1"></i>If you change your email address, you will need to verify the new email address.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">{{ __('Phone Number') }} <small class="text-muted">(Philippines +63, 10 digits)</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone-fill me-1"></i>+63</span>
                        <input id="phone" type="tel" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ? preg_replace('/^\+63/', '', $user->phone) : '') }}" placeholder="9123456789" autocomplete="tel" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" title="10-digit Philippine mobile number">
                    </div>
                    @error('phone')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <input type="hidden" id="full_phone" name="phone" value="{{ old('phone', $user->phone) }}">
                    <div id="phone-status" class="mt-2">
                        @if($user->phone_verified_at)
                            <small class="text-success"><i class="bi bi-check-circle me-1"></i>Phone verified</small>
                        @elseif($user->phone)
                            <small class="text-warning"><i class="bi bi-exclamation-circle me-1"></i>Phone not verified</small>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-warning" id="verify-phone-btn">
                                    <i class="bi bi-shield-check me-1"></i>Verify Phone Number
                                </button>
                            </div>
                        @else
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>No phone number added</small>
                            <div class="mt-2">
                                <small class="text-muted d-block">Add a phone number and verify it to enhance your account security.</small>
                                <button type="button" class="btn btn-sm btn-outline-warning mt-2" id="verify-phone-btn" disabled title="Enter a valid 10-digit phone number first">
                                    <i class="bi bi-shield-check me-1"></i>Send OTP to Verify
                                </button>
                            </div>
                        @endif
                    </div>
                    <div id="otp-message" class="mt-2"></div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

                    @if (session('status') === 'profile-updated')
                        <p class="text-success mb-0">
                            <i class="bi bi-check-circle me-1"></i>{{ __('Saved.') }}
                        </p>
                    @endif
                    
                    @if (session('email-verification-sent'))
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1"></i>{{ session('email-verification-sent') }}
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time email checking for email change
    const emailInput = document.getElementById('email');
    const emailFeedback = document.getElementById('email-check-feedback');
    const originalEmail = '{{ $user->email }}';
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim().toLowerCase();
            
            if (email === '' || email === originalEmail.toLowerCase()) {
                emailFeedback.innerHTML = '';
                this.classList.remove('is-invalid', 'is-valid');
                return;
            }
            
            // Simple email format check
            if (!email.includes('@')) {
                return;
            }
            
            fetch('{{ route('profile.check-email') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    emailFeedback.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle me-1"></i>' + (data.message || 'This email is already in use.') + '</small>';
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

    // Verify email button: POST to verification.send (form was nested and never submitted)
    const verifyEmailBtn = document.getElementById('verify-email-btn');
    if (verifyEmailBtn) {
        verifyEmailBtn.addEventListener('click', function() {
            const btn = this;
            const btnText = document.getElementById('verify-email-btn-text');
            const spinner = document.getElementById('verify-email-spinner');
            const errorEl = document.getElementById('verify-email-error');
            if (errorEl) { errorEl.classList.add('d-none'); errorEl.textContent = ''; }
            btn.disabled = true;
            if (btnText) btnText.textContent = 'Sending...';
            if (spinner) spinner.classList.remove('d-none');

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('{{ url(route("verification.send")) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }
                if (response.ok) {
                    window.location.reload();
                    return;
                }
                return response.json().then(function(data) {
                    throw new Error(data.message || data.error || 'Failed to send verification email.');
                }).catch(function(e) {
                    if (e instanceof SyntaxError) {
                        throw new Error('Failed to send verification email. Please try again.');
                    }
                    throw e;
                });
            })
            .catch(function(err) {
                if (errorEl) {
                    errorEl.textContent = err.message || 'Failed to send. Please try again.';
                    errorEl.classList.remove('d-none');
                }
                btn.disabled = false;
                if (btnText) btnText.textContent = 'Verify Email Address';
                if (spinner) spinner.classList.add('d-none');
            });
        });
    }

    // Phone: sync 10-digit input to full_phone hidden field (+63...) for form submit
    const phoneInput = document.getElementById('phone');
    const fullPhoneInput = document.getElementById('full_phone');
    if (phoneInput && fullPhoneInput) {
        phoneInput.addEventListener('input', function() {
            const phoneValue = this.value.replace(/\D/g, '').slice(0, 10);
            this.value = phoneValue;
            fullPhoneInput.value = phoneValue.length === 10 ? '+63' + phoneValue : '';
        });
        phoneInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
