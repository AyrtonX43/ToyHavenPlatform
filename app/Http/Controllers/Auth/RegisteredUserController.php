<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required', 
                'confirmed', 
                'min:8',
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[^A-Za-z0-9]/', // must contain a special character
            ],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password_confirmation.same' => 'The password confirmation does not match. Please enter the same password.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Fire Registered event (sends email verification)
        // Redirect to verify-email page so user can resend if email doesn't arrive
        try {
            usleep(500000); // Small delay to avoid rate limiting
            event(new Registered($user));
            return redirect()->route('verification.notice')
                ->with('status', 'verification-link-sent');
        } catch (\Throwable $e) {
            Log::error('Failed to send email verification notification during registration', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $message = config('app.debug')
                ? 'Mail error: ' . $e->getMessage()
                : 'We couldn\'t send the verification email. Click the button below to try again.';
            return redirect()->route('verification.notice')
                ->with('warning', $message);
        }
    }
}
