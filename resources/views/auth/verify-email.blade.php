<x-guest-layout>
    <div class="text-center mb-4">
        <i class="bi bi-envelope-check text-primary" style="font-size: 4rem;"></i>
    </div>
    
    <h3 class="text-center mb-4 fw-bold reveal">Verify Your Email</h3>
    <p class="text-center text-muted mb-4 reveal animate-delay-1">
        Thanks for signing up! We'll send a verification link to your email when this page loads. You must wait <strong id="timerLabel">30 seconds</strong> before you can resend. Check your inbox and spam folder.
    </p>

    <div id="autoSendAlert" class="alert alert-secondary alert-dismissible fade show reveal mb-3 d-none" role="alert">
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        <span id="autoSendAlertText">Sending verification email...</span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success alert-dismissible fade show reveal" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            A new verification link has been sent to <strong>{{ auth()->user()->email ?? 'your email' }}</strong>. Check your inbox and spam folder. The link is valid for 24 hours.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show reveal" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show reveal" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show reveal" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show reveal" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Error:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="reveal animate-delay-2">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small text-muted">Resend available in:</span>
            <span id="countdown" class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 1.1rem;">30</span>
        </div>
        <form method="POST" action="{{ route('verification.send') }}" id="resendVerificationForm" class="mb-3">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2" id="resendBtn" disabled>
                    <i class="bi bi-envelope-paper me-2"></i><span id="resendBtnText">Resend available in 30s</span>
                    <span class="spinner-border spinner-border-sm d-none" id="resendSpinner" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-outline-secondary py-2">
                    <i class="bi bi-box-arrow-right me-2"></i>Log Out
                </button>
            </div>
        </form>
    </div>

    <script>
        (function() {
            var resendBtn = document.getElementById('resendBtn');
            var resendBtnText = document.getElementById('resendBtnText');
            var resendSpinner = document.getElementById('resendSpinner');
            var countdownEl = document.getElementById('countdown');
            var timerLabel = document.getElementById('timerLabel');
            var autoSendAlert = document.getElementById('autoSendAlert');
            var autoSendAlertText = document.getElementById('autoSendAlertText');
            var secondsLeft = 30;
            var countdownInterval = null;
            var csrfToken = document.querySelector('input[name="_token"]').value;
            var sendUrl = '{{ route("verification.send") }}';

            function startCountdown(resetIfRunning) {
                if (countdownInterval && !resetIfRunning) return;
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                }
                secondsLeft = 30;
                resendBtn.disabled = true;
                resendBtnText.textContent = 'Resend available in 30s';
                countdownEl.textContent = '30';
                countdownEl.classList.remove('bg-success');
                countdownEl.classList.add('bg-primary');

                countdownInterval = setInterval(function() {
                    secondsLeft--;
                    countdownEl.textContent = secondsLeft;
                    resendBtnText.textContent = 'Resend available in ' + secondsLeft + 's';
                    if (secondsLeft <= 0) {
                        clearInterval(countdownInterval);
                        countdownInterval = null;
                        resendBtn.disabled = false;
                        resendBtnText.textContent = 'Resend Verification Email';
                        countdownEl.textContent = '0';
                        countdownEl.classList.remove('bg-primary');
                        countdownEl.classList.add('bg-success');
                    }
                }, 1000);
            }

            function showAutoSendResult(success, message) {
                autoSendAlert.classList.remove('alert-secondary', 'alert-success', 'alert-danger');
                autoSendAlert.classList.add(success ? 'alert-success' : 'alert-danger');
                var spinner = autoSendAlert.querySelector('.spinner-border');
                if (spinner) spinner.classList.add('d-none');
                autoSendAlertText.textContent = message;
                autoSendAlert.classList.remove('d-none');
            }

            // Always auto-send verification email when this page loads (user is unverified)
            autoSendAlert.classList.remove('d-none');
            autoSendAlertText.textContent = 'Sending verification email...';
            var autoSendResolved = false;
            var fetchController = typeof AbortController !== 'undefined' ? new AbortController() : null;
            var fetchTimeout = window.setTimeout(function() {
                if (autoSendResolved) return;
                autoSendResolved = true;
                if (fetchController) fetchController.abort();
                showAutoSendResult(false, 'Email is taking longer than expected. You can try again using the button below when it becomes available.');
            }, 32000);

            fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({}),
                credentials: 'same-origin',
                signal: fetchController ? fetchController.signal : undefined
            }).then(function(r) {
                if (autoSendResolved) return;
                window.clearTimeout(fetchTimeout);
                autoSendResolved = true;
                return r.json().then(function(data) {
                    if (data.status === 'verification-link-sent' || data.status === 'already-verified') {
                        showAutoSendResult(true, data.message || 'Verification email sent! Check your inbox and spam folder.');
                    } else {
                        showAutoSendResult(false, data.message || 'Could not send. Use the button below when it becomes available.');
                    }
                }).catch(function() {
                    showAutoSendResult(r.ok, r.ok ? 'Verification email sent!' : (r.status === 429 ? 'Too many attempts. Please wait a moment.' : 'Request failed. Use the button below when it becomes available.'));
                });
            }).catch(function(err) {
                if (autoSendResolved) return;
                window.clearTimeout(fetchTimeout);
                autoSendResolved = true;
                var msg = err.name === 'AbortError' ? 'Email is taking longer than expected. You can try again using the button below when it becomes available.' : 'Could not send automatically. Use the button below when it becomes available.';
                showAutoSendResult(false, msg);
            });

            // Start 30-second countdown so resend button unlocks after 30 seconds no matter what
            startCountdown();

            document.getElementById('resendVerificationForm').addEventListener('submit', function(e) {
                if (resendBtn.disabled) {
                    e.preventDefault();
                    return;
                }
                resendBtn.disabled = true;
                resendBtnText.textContent = 'Sending...';
                resendSpinner.classList.remove('d-none');
                startCountdown(true);
            });
        })();
    </script>
</x-guest-layout>
