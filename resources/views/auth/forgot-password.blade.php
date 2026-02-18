<x-guest-layout>
    <h3 class="text-center mb-4 fw-bold reveal">Reset Password</h3>
    
    @if(session('confirm_account'))
        <!-- Step 2: Account Confirmation -->
        @if(session('email_sent'))
            <!-- Email Sent Successfully -->
            <p class="text-center text-muted mb-4 reveal animate-delay-1">
                Password reset link has been sent!
            </p>

            <!-- Success Message -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show reveal" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="reveal animate-delay-2">
                <div class="card border-success mb-4">
                    <div class="card-body text-center">
                        <i class="bi bi-envelope-check text-success" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3 mb-3">
                            Email Sent Successfully
                        </h5>
                        <p class="text-muted mb-0">
                            We've sent a password reset link to:<br>
                            <strong>{{ session('user_email') }}</strong>
                        </p>
                        <p class="text-muted mt-2 mb-0">
                            Please check your email inbox and spam folder. The link will expire in 60 minutes.
                        </p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('password.reset.confirm') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('user_email') }}">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-outline-primary py-2 w-100">
                            <i class="bi bi-arrow-clockwise me-2"></i>Resend Email
                        </button>
                    </form>
                    <a href="{{ route('login') }}" class="btn btn-primary py-2">
                        <i class="bi bi-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </div>
        @else
            <!-- Account Confirmation (Before Sending) -->
            <p class="text-center text-muted mb-4 reveal animate-delay-1">
                Please confirm if this account belongs to you.
            </p>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show reveal" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Session Status (for resend attempts) -->
            @if (session('status') && !session('email_sent'))
                <div class="alert alert-info alert-dismissible fade show reveal" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="reveal animate-delay-2">
                <div class="card border-primary mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-person-circle me-2"></i>Account Information
                        </h5>
                        <div class="mb-3">
                            <strong><i class="bi bi-person me-2"></i>Full Name:</strong>
                            <p class="mb-0 mt-1">{{ session('user_name') }}</p>
                        </div>
                        <div class="mb-0">
                            <strong><i class="bi bi-envelope me-2"></i>Email Address:</strong>
                            <p class="mb-0 mt-1">{{ session('user_email') }}</p>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <!-- Yes Button Form -->
                    <form method="POST" action="{{ route('password.reset.confirm') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('user_email') }}">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-primary py-2 w-100">
                            <i class="bi bi-check-circle me-2"></i>Yes, this is my account
                        </button>
                    </form>

                    <!-- No Button Form -->
                    <form method="POST" action="{{ route('password.reset.confirm') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="email" value="{{ session('user_email') }}">
                        <input type="hidden" name="confirm" value="no">
                        <button type="submit" class="btn btn-outline-secondary py-2 w-100">
                            <i class="bi bi-x-circle me-2"></i>No, this is not my account
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @else
        <!-- Step 1: Email Input -->
        <p class="text-center text-muted mb-4 reveal animate-delay-1">
            Forgot your password? Enter your email address to get started.
        </p>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show reveal" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="reveal animate-delay-2">
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
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
                        placeholder="Enter your email address">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2">
                        <i class="bi bi-arrow-right me-2"></i>Continue
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
</x-guest-layout>
