<x-guest-layout>
    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show reveal" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <h3 class="text-center mb-4 fw-bold reveal">Sign In</h3>
    <p class="text-center text-muted mb-4 reveal animate-delay-1">Sign in to your account to continue</p>

    <form method="POST" action="{{ route('login') }}" class="reveal animate-delay-2">
        @csrf

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
                autofocus 
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
                autocomplete="current-password"
                placeholder="Enter your password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Forgot Password -->
        <div class="d-flex justify-content-end align-items-center mb-4">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none text-primary">
                    Forgot password?
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>

        <!-- Divider -->
        <div class="position-relative my-4">
            <hr>
            <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">
                Or continue with
            </div>
        </div>

        <!-- Google Login Button -->
        <a href="{{ route('google.auth') }}" class="btn btn-outline-secondary w-100 py-2">
            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <!-- Register Link -->
        <div class="text-center mt-4">
            <p class="text-muted mb-0">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Sign up</a>
            </p>
        </div>
    </form>

    <script>
        // Real-time email existence check on sign in
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
                    feedbackDiv.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i><span>Email found. You can proceed to sign in.</span></small>';
                    feedbackDiv.className = 'form-feedback';
                } else {
                    feedbackDiv.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i><span>Email not found. Please register first.</span></small>';
                    feedbackDiv.className = 'form-feedback';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</x-guest-layout>
