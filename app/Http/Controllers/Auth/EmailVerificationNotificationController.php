<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            $wantsJson = $request->wantsJson() || $request->ajax();
            if ($wantsJson) {
                return response()->json(['status' => 'error', 'message' => 'You must be logged in to request a verification email.'], 401);
            }
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'already-verified', 'message' => 'Your email is already verified.']);
            }
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('info', 'Your email is already verified.');
        }

        try {
            // Allow up to 60 seconds for SMTP to send
            if (function_exists('set_time_limit')) {
                @set_time_limit(60);
            }
            usleep(500000); // 0.5 second delay to avoid rate limiting

            $mailDriver = config('mail.default');
            $actuallySends = ! in_array($mailDriver, ['log', 'array'], true);

            $user->sendEmailVerificationNotification();

            Log::info('Email verification link sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'driver' => $mailDriver,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'verification-link-sent',
                    'message' => $actuallySends
                        ? 'Verification link sent to your email. Check your inbox and spam folder.'
                        : 'Mail is logged only in this environment. Check storage/logs/laravel.log for the link.',
                    'actually_sends' => $actuallySends,
                ]);
            }

            if ($actuallySends) {
                return back()->with('status', 'verification-link-sent');
            }
            return back()->with('info', 'Verification emails are not sent to your inbox in this environment (mail is logged). Check storage/logs/laravel.log for the verification link.');
        } catch (\Throwable $e) {
            Log::error('Failed to send email verification', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = str_contains($e->getMessage(), 'Too many emails')
                ? 'Email sending is temporarily limited. Please wait a moment and try again.'
                : (config('app.debug') ? 'Mail error: ' . $e->getMessage() : 'Failed to send verification email. Please try again later or contact support.');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $errorMessage], 500);
            }

            if (str_contains($e->getMessage(), 'Too many emails')) {
                return back()->with('error', 'Email sending is temporarily limited. Please wait a moment and try again.');
            }
            return back()->with('error', $errorMessage);
        }
    }
}
