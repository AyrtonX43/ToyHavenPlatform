<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Double-check if user is banned (in case they got through somehow)
        if ($user && ($user->is_banned ?? false)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Your account has been banned. Please contact support for more information.');
        }

        // Check if user's seller account is suspended
        if ($user->seller && !$user->seller->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Your business account has been suspended. Please contact support for more information.');
        }

        // If email is not verified, send verification link now and show the notice
        if (!$user->hasVerifiedEmail()) {
            $mailDriver = config('mail.default');
            $actuallySends = !in_array($mailDriver, ['log', 'array'], true);

            try {
                // Allow up to 60 seconds for SMTP to send (user can wait 30–50 sec for automatic send)
                if (function_exists('set_time_limit')) {
                    @set_time_limit(60);
                }
                usleep(500000); // 0.5s delay to avoid rate limiting
                $user->sendEmailVerificationNotification();
                Log::info('Email verification notification sent on login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'driver' => $mailDriver,
                ]);
                if ($actuallySends) {
                    return redirect()->route('verification.notice')
                        ->with('status', 'verification-link-sent');
                }
                return redirect()->route('verification.notice')
                    ->with('info', 'Verification emails are not sent to your inbox in this environment (mail is logged). Use the button below to resend—the link may appear in the application log.');
            } catch (\Throwable $e) {
                Log::warning('Failed to send verification email on login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('verification.notice')
                    ->with('warning', 'Please verify your email address. We couldn\'t send a new link right now—use the button below to resend.');
            }
        }

        // Redirect based on user role
        if ($user->isAdmin()) {
            // Clear any intended URL to ensure admin always goes to admin dashboard
            $request->session()->forget('url.intended');
            return redirect()->route('admin.dashboard');
        }

        if ($user->isSeller()) {
            return redirect()->intended(route('seller.dashboard', absolute: false));
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $wasAdmin = auth()->check() && auth()->user()->isAdmin();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // If admin logged out, redirect to home with message
        if ($wasAdmin) {
            return redirect('/')->with('success', 'You have been logged out successfully.');
        }

        return redirect('/');
    }

    /**
     * Check if email exists
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }
}
