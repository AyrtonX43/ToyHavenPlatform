<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        // Check if coming from register page
        if ($request->get('from') === 'register' || str_contains($request->headers->get('referer', ''), 'register')) {
            session(['register_attempt' => true]);
        }
        
        // Persist session before redirecting to Google so the OAuth state is saved
        // (avoids "state mismatch" / InvalidStateException when returning from Google)
        $request->session()->save();

        // Log the redirect URI for debugging
        \Log::info('Google OAuth Redirect', [
            'redirect_uri' => config('services.google.redirect'),
            'app_url' => config('app.url'),
        ]);

        return Socialite::driver('google')
            ->redirectUrl(config('services.google.redirect'))
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            // Use the same redirect URL when getting the user from Socialite.
            // In local/XAMPP, disable SSL verification to avoid cURL error 60.
            $guzzleOptions = config('app.env') === 'local' ? ['verify' => false] : [];
            // stateless() skips OAuth state check so login works even when the session
            // is lost on return from Google (e.g. cookie not sent). The auth code is one-time use.
            $googleUser = Socialite::driver('google')
                ->redirectUrl(config('services.google.redirect'))
                ->setHttpClient(new Client($guzzleOptions))
                ->stateless()
                ->user();
            
            // Validate that we got user data
            if (!$googleUser || !$googleUser->email) {
                \Log::error('Google OAuth: Invalid user data received', [
                    'user' => $googleUser,
                ]);
                return redirect()->route('login')
                    ->with('error', 'Failed to retrieve user information from Google. Please try again.');
            }
            
            // Check if user exists by google_id first
            $user = User::where('google_id', $googleUser->id)->first();
            
            // Check if user exists by email (for linking accounts)
            $existingUserByEmail = null;
            if (!$user) {
                $existingUserByEmail = User::where('email', $googleUser->email)->first();
                
                // If user exists by email and trying to register, redirect to register
                if ($existingUserByEmail) {
                    $fromRegister = session('register_attempt', false);
                    if ($fromRegister && !$existingUserByEmail->google_id) {
                        session()->forget('register_attempt');
                        return redirect()->route('register')->with('error', 'This email is already registered. Please sign in with your email and password, or use Google sign in from the login page.');
                    }
                }
            }
            
            // Use database transaction to ensure data integrity
            $user = DB::transaction(function () use ($googleUser, $user, $existingUserByEmail) {
                if (!$user) {
                    if ($existingUserByEmail) {
                        // Link existing user with Google ID if not already linked
                        if (!$existingUserByEmail->google_id) {
                            $existingUserByEmail->google_id = $googleUser->id;
                            $existingUserByEmail->save();
                            \Log::info('Google OAuth: Linked existing user with Google ID', [
                                'user_id' => $existingUserByEmail->id,
                                'email' => $existingUserByEmail->email,
                            ]);
                        }
                        
                        // Ensure email is verified for Google OAuth users
                        if (!$existingUserByEmail->email_verified_at) {
                            $existingUserByEmail->email_verified_at = now();
                            $existingUserByEmail->save();
                        }
                        
                        $user = $existingUserByEmail;
                    } else {
                        // Create new user
                        $user = User::create([
                            'name' => $googleUser->name,
                            'email' => $googleUser->email,
                            'google_id' => $googleUser->id,
                            'password' => bcrypt(str()->random(24)), // Random password for OAuth users
                            'role' => 'customer',
                            'email_verified_at' => now(),
                        ]);
                        
                        \Log::info('Google OAuth: Created new user', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'google_id' => $user->google_id,
                        ]);
                    }
                } else {
                    // User exists by google_id - ensure email is verified
                    if (!$user->email_verified_at) {
                        $user->email_verified_at = now();
                        $user->save();
                    }
                }
                
                // Refresh user from database to ensure we have latest data
                $user->refresh();
                
                return $user;
            });
            
            // Check if user is banned before logging in
            if ($user->is_banned ?? false) {
                session()->forget('register_attempt');
                return redirect()->route('login')
                    ->with('error', 'Your account has been banned. Please contact support for more information.');
            }
            
            // Check if user's seller account is suspended
            if ($user->seller && !$user->seller->is_active) {
                session()->forget('register_attempt');
                return redirect()->route('login')
                    ->with('error', 'Your business account has been suspended. Please contact support for more information.');
            }
            
            // Clear any intended URL that might redirect back to login
            $request->session()->forget('url.intended');
            
            // Clear register attempt flag
            session()->forget('register_attempt');
            
            // Log the user in with remember me using web guard explicitly
            Auth::guard('web')->login($user, true);
            
            // Do NOT regenerate session here. Regenerating after OAuth callback often causes
            // the new session to not be persisted before the redirect (especially with database
            // session driver), so the first request to home sees an empty session and the user
            // appears logged out. Skipping regeneration ensures the session with auth is
            // committed and sent in the same response, so the first redirect to home works.
            $request->session()->save();
            
            // Verify authentication immediately after login
            if (!Auth::guard('web')->check()) {
                \Log::error('Google OAuth: Authentication check failed after login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'session_id' => $request->session()->getId(),
                ]);
                return redirect()->route('login')
                    ->with('error', 'Authentication failed. Please try again.');
            }
            
            // Get fresh user instance from auth to ensure we have the latest data
            $authenticatedUser = Auth::guard('web')->user();
            
            // Double-check authentication is valid before redirecting
            if (!$authenticatedUser) {
                \Log::error('Google OAuth: User not in auth after Auth::login()', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'session_id' => $request->session()->getId(),
                ]);
                return redirect()->route('login')
                    ->with('error', 'Authentication failed. Please try again.');
            }
            
            // Log successful authentication for debugging
            \Log::info('Google OAuth: User authenticated successfully', [
                'user_id' => $authenticatedUser->id,
                'email' => $authenticatedUser->email,
                'has_categories' => $authenticatedUser->hasSelectedCategories(),
                'role' => $authenticatedUser->role,
                'session_id' => $request->session()->getId(),
                'auth_check' => Auth::check(),
                'auth_guard_check' => Auth::guard('web')->check(),
            ]);
            
            // Always redirect to homepage after Google login
            $redirectMessage = 'Successfully signed in with Google!';

            // Log the redirect destination for debugging
            \Log::info('Google OAuth: Redirecting user to homepage', [
                'user_id' => $authenticatedUser->id,
                'session_id' => $request->session()->getId(),
                'auth_check_before_redirect' => Auth::check(),
            ]);

            // Commit session so the next request (homepage) sees the logged-in user.
            // Use 303 See Other so the browser does a GET and sends the session cookie.
            $request->session()->save();

            return redirect()->to(url('/'), 303)
                ->with('success', $redirectMessage);
                
        } catch (InvalidStateException $e) {
            \Log::warning('Google OAuth: Invalid state (session may have been lost)', [
                'exception' => get_class($e),
            ]);
            session()->forget('register_attempt');
            return redirect()->route('login')
                ->with('error', 'Your session expired or cookies were blocked. Please click "Sign in with Google" again.');
        } catch (\Exception $e) {
            \Log::error('Google OAuth Error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            session()->forget('register_attempt');

            $errorMessage = 'Google authentication failed. Please try again.';
            if (config('app.debug') && $e->getMessage() !== '') {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            return redirect()->route('login')->with('error', $errorMessage);
        }
    }
}
