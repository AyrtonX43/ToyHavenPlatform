<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PhoneOtpPasswordResetController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send OTP to phone for password reset
     */
    public function sendOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $email = strtolower($request->email);
        $phone = $request->phone;
        $name = $request->name;

        // Verify user account exists and matches provided information
        $user = User::where('email', $email)->first();

        if (!$user) {
            // For security, don't reveal if email exists
            return response()->json([
                'success' => false,
                'message' => 'If an account exists with this information, an OTP has been sent.',
            ], 200);
        }

        // Verify phone number matches (if user has phone)
        if ($user->phone && $user->phone !== $phone) {
            return response()->json([
                'success' => false,
                'message' => 'The phone number does not match the account.',
            ], 422);
        }

        // Verify name matches (case-insensitive)
        if (strtolower($user->name) !== strtolower($name)) {
            return response()->json([
                'success' => false,
                'message' => 'The name does not match the account.',
            ], 422);
        }

        // Invalidate previous OTPs for this email/phone
        PasswordResetOtp::where('email', $email)
            ->where('phone', $phone)
            ->where('verified', false)
            ->update(['verified' => true]);

        // Generate OTP
        $otp = PasswordResetOtp::generate();

        // Store OTP (expires in 10 minutes)
        PasswordResetOtp::create([
            'email' => $email,
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send SMS
        $message = "Your ToyHaven password reset code is: {$otp}. Valid for 10 minutes.";
        $smsSent = $this->smsService->send($phone, $message);

        if (!$smsSent) {
            Log::error('Failed to send password reset OTP SMS', [
                'email' => $email,
                'phone' => $phone,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.',
            ], 500);
        }

        Log::info('Password reset OTP sent', [
            'email' => $email,
            'phone' => $phone,
            // In development, log OTP for testing
            'otp' => config('app.debug') ? $otp : '***',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully. Please check your phone.',
            // Only include OTP in development for testing
            'otp' => config('app.debug') ? $otp : null,
        ]);
    }

    /**
     * Verify OTP for password reset
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower($request->email);
        $phone = $request->phone;
        $otp = $request->otp;

        $passwordResetOtp = PasswordResetOtp::where('email', $email)
            ->where('phone', $phone)
            ->where('otp', $otp)
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$passwordResetOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code.',
            ], 422);
        }

        if ($passwordResetOtp->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ], 422);
        }

        // Mark OTP as verified
        $passwordResetOtp->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. You can now reset your password.',
        ]);
    }

    /**
     * Reset password using verified OTP
     */
    public function resetWithOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $email = strtolower($request->email);
        $phone = $request->phone;
        $otp = $request->otp;

        // Verify OTP is valid and verified
        $passwordResetOtp = PasswordResetOtp::where('email', $email)
            ->where('phone', $phone)
            ->where('otp', $otp)
            ->where('verified', true)
            ->latest()
            ->first();

        if (!$passwordResetOtp) {
            return back()->withInput($request->only('email', 'phone'))
                ->withErrors(['otp' => 'Invalid or unverified OTP.']);
        }

        if ($passwordResetOtp->isExpired()) {
            return back()->withInput($request->only('email', 'phone'))
                ->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        // Check if OTP was verified recently (within last hour)
        if ($passwordResetOtp->verified_at && $passwordResetOtp->verified_at->lt(now()->subHour())) {
            return back()->withInput($request->only('email', 'phone'))
                ->withErrors(['otp' => 'OTP verification has expired. Please start over.']);
        }

        // Find user and update password
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withInput($request->only('email', 'phone'))
                ->withErrors(['email' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate all OTPs for this email/phone
        PasswordResetOtp::where('email', $email)
            ->where('phone', $phone)
            ->update(['verified' => true]);

        Log::info('Password reset via OTP successful', [
            'user_id' => $user->id,
            'email' => $email,
        ]);

        return redirect()->route('login')
            ->with('status', 'Your password has been reset successfully. Please login with your new password.');
    }
}
