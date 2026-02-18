<x-guest-layout>
    <div class="text-center mb-4">
        <i class="bi bi-shield-lock text-primary" style="font-size: 4rem;"></i>
    </div>
    
    <h3 class="text-center mb-4 fw-bold reveal">Confirm Password</h3>
    <p class="text-center text-muted mb-4 reveal animate-delay-1">
        This is a secure area of the application. Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="reveal animate-delay-2">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label fw-semibold">
                <i class="bi bi-lock me-2"></i>Password
            </label>
            <div class="position-relative">
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    required 
                    autocomplete="current-password"
                    placeholder="Enter your password">
                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" onclick="togglePassword('password')" style="text-decoration: none; border: none; background: none;">
                    <i class="bi bi-eye" id="password-toggle"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary py-2">
                <i class="bi bi-check-circle me-2"></i>Confirm
            </button>
        </div>
    </form>

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
    </script>
</x-guest-layout>
