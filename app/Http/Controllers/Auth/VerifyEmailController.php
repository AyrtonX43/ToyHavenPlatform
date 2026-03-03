<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $redirectTo = match (true) {
            $user->isAdmin() => route('admin.analytics.index'),
            $user->isModerator() => route('moderator.dashboard'),
            $user->isSeller() || $user->canAccessSellerDashboard() => route('seller.dashboard'),
            default => route('dashboard', absolute: false),
        };

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($redirectTo)
                ->with('info', 'Your email is already verified.');
        }

        try {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
                
                Log::info('Email verified successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                return redirect()->intended($redirectTo)
                    ->with('success', 'Your email has been verified successfully!');
            }
        } catch (\Exception $e) {
            Log::error('Failed to verify email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('verification.notice')
                ->with('error', 'Failed to verify email. Please try again or contact support.');
        }

        return redirect()->intended($redirectTo . '?verified=1');
    }
}
