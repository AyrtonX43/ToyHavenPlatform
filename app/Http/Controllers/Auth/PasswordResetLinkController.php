<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request (Step 1: Check email).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower($request->email);

        // Check if user exists
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            // Email is not registered
            return back()->withInput($request->only('email'))
                        ->withErrors(['email' => 'The email address you entered is not registered on our website. Please enter the exact email address.']);
        }

        // Email exists - redirect to confirmation page
        return redirect()->route('password.request')
                    ->with('confirm_account', true)
                    ->with('user_email', $user->email)
                    ->with('user_name', $user->name);
    }

    /**
     * Handle account confirmation and send password reset link (Step 2).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'confirm' => ['required', 'in:yes,no'],
        ]);

        $email = strtolower($request->email);
        $confirm = $request->confirm;

        if ($confirm === 'no') {
            // User says it's not their account - redirect back to step 1
            return redirect()->route('password.request')
                        ->withInput(['email' => $email])
                        ->withErrors(['email' => 'Please enter the exact email address for your account.']);
        }

        // Get user info for confirmation page
        $user = \App\Models\User::where('email', $email)->first();
        
        // User confirmed it's their account - send reset link
        // Add small delay to prevent Mailtrap rate limiting
        usleep(500000); // 0.5 second delay
        
        $status = Password::sendResetLink(
            ['email' => $email]
        );

        // Log the status for debugging
        Log::info('Password reset link requested', [
            'email' => $email,
            'status' => $status,
            'mail_driver' => config('mail.default'),
        ]);

        if ($status == Password::RESET_LINK_SENT) {
            // Email sent successfully - show success message on confirmation page
            return redirect()->route('password.request')
                        ->with('confirm_account', true)
                        ->with('user_email', $user->email)
                        ->with('user_name', $user->name)
                        ->with('status', 'We have emailed your password reset link! Please check your email inbox.')
                        ->with('email_sent', true);
        }

        // Handle specific error cases - show error on confirmation page with resend option
        if ($status == Password::RESET_THROTTLED) {
            $errorMessage = 'Please wait before retrying. Too many reset attempts.';
        } else {
            $errorMessage = 'Unable to send password reset link. Please try again.';
        }

        return redirect()->route('password.request')
                    ->with('confirm_account', true)
                    ->with('user_email', $user->email)
                    ->with('user_name', $user->name)
                    ->with('email_sent', false)
                    ->withErrors(['email' => $errorMessage]);
    }
}
