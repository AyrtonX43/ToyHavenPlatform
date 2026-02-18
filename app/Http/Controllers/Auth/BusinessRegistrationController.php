<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\Seller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BusinessRegistrationController extends Controller
{
    /**
     * Display the business registration view.
     */
    public function create(Request $request): View
    {
        $type = $request->query('type', 'basic');
        $categories = Category::where('is_active', true)->orderBy('display_order')->orderBy('name')->get();
        return view('auth.business-register', compact('type', 'categories'));
    }

    /**
     * Handle an incoming business registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $registrationType = $request->input('registration_type', 'basic');
        $isVerified = $registrationType === 'verified';

        // Validation rules for user account
        $userRules = [
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required', 
                'confirmed', 
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'password_confirmation' => ['required', 'same:password'],
        ];

        // Validation rules for business information
        $businessRules = [
            'business_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'business_email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];

        // Add verified shop requirements
        if ($isVerified) {
            $businessRules['business_permit'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $businessRules['bank_account'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        // Merge and validate all rules
        $rules = array_merge($userRules, $businessRules);
        
        $request->validate($rules, [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password_confirmation.same' => 'The password confirmation does not match.',
            'phone.regex' => 'Please enter a valid 10-digit Philippine phone number (e.g. 9123456789).',
        ]);

        // Create user account with seller role
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'seller',
            'email_verified_at' => now(), // Auto-verify for business accounts
        ]);

        // Create seller record
        $seller = Seller::create([
            'user_id' => $user->id,
            'business_name' => $request->business_name,
            'business_slug' => Str::slug($request->business_name) . '-' . $user->id,
            'description' => $request->description,
            'phone' => $request->phone,
            'email' => $request->business_email,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'toy_category_ids' => $request->toy_category_ids,
            'verification_status' => 'pending',
            'is_verified_shop' => $isVerified,
        ]);

        // Upload Primary ID document (required for all)
        $idPath = $request->file('id_document')->store('seller_documents/' . $seller->id, 'public');
        \App\Models\SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => 'id',
            'document_path' => $idPath,
            'status' => 'pending',
        ]);

        // Upload Bank document (required for all)
        $bankPath = $request->file('bank_document')->store('seller_documents/' . $seller->id, 'public');
        \App\Models\SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => 'bank_account',
            'document_path' => $bankPath,
            'status' => 'pending',
        ]);

        // Upload verified trusted seller documents (only if verified registration)
        if ($isVerified) {
            $businessRegPath = $request->file('business_registration')->store('seller_documents/' . $seller->id, 'public');
            \App\Models\SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'business_registration',
                'document_path' => $businessRegPath,
                'status' => 'pending',
            ]);

            $brandRightsPath = $request->file('brand_rights')->store('seller_documents/' . $seller->id, 'public');
            \App\Models\SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'brand_rights',
                'document_path' => $brandRightsPath,
                'status' => 'pending',
            ]);
        }

        // Do not fire Registered event: business accounts are auto-verified (email_verified_at set above).
        // Firing it would send a verification email via SMTP and can cause request timeouts if SMTP is slow/unreachable.

        Auth::login($user);

        // Create detailed admin approval notification message
        $restrictedFeatures = [
            'Seller Dashboard Tools',
            'Upload Products (Toyshop, Trading, Auction)',
            'View Business Page',
            'Product Tracking',
            'Chat System',
            'Order Management',
            'Analytics & Reports'
        ];

        $message = $isVerified 
            ? 'Full verified trusted shop registration submitted successfully!'
            : 'Business account registration submitted successfully!';

        $infoMessage = 'Your registration is pending admin approval. You can browse and shop online, but business features will be available after approval.';

        return redirect()->route('seller.dashboard')
            ->with('success', $message)
            ->with('info', $infoMessage)
            ->with('pending_approval', true)
            ->with('restricted_features', $restrictedFeatures);
    }

    /**
     * Check if email exists (for real-time validation)
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'type' => 'required|in:personal,business',
        ]);

        $email = strtolower($request->email);
        $type = $request->type;

        if ($type === 'personal') {
            // Check if email exists in users table
            $exists = User::where('email', $email)->exists();
            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 'This email is already registered.' : 'Email is available.',
            ]);
        } else {
            // Check if email exists in sellers table (business_email)
            $exists = Seller::where('email', $email)->exists();
            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 'This business email is already in use.' : 'Business email is available.',
            ]);
        }
    }
}
