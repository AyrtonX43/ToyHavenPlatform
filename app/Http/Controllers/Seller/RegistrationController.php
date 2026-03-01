<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function show(Request $request)
    {
        if (Auth::user()->seller) {
            return redirect()->route('seller.dashboard')
                ->with('info', 'You are already registered as a seller.');
        }

        // Check if type parameter is present to show form
        $type = $request->query('type');
        
        if ($type && in_array($type, ['basic', 'verified'])) {
            $user = Auth::user();
            
            // Get default address or fallback to user's address fields
            $defaultAddress = $user->defaultAddress;
            
            // Prepare pre-filled data
            $phoneDisplay = '';
            if ($user->phone) {
                $phoneDisplay = strpos($user->phone, '+63') === 0 ? substr($user->phone, 3) : $user->phone;
            }
            
            $prefilledData = [
                'phone' => $phoneDisplay,
                'email' => $user->email ?? '',
                'address' => $defaultAddress ? $defaultAddress->address : ($user->address ?? ''),
                'region' => $defaultAddress ? $defaultAddress->region : ($user->region ?? ''),
                'city' => $defaultAddress ? $defaultAddress->city : ($user->city ?? ''),
                'barangay' => $defaultAddress ? $defaultAddress->barangay : ($user->barangay ?? ''),
                'province' => $defaultAddress ? $defaultAddress->province : ($user->province ?? ''),
                'postal_code' => $defaultAddress ? $defaultAddress->postal_code : ($user->postal_code ?? ''),
            ];
            
            $categories = Category::where('is_active', true)->orderBy('display_order')->orderBy('name')->get();
            return view('seller.registration.form', compact('type', 'prefilledData', 'categories'));
        }

        return view('seller.registration.index');
    }

    public function store(Request $request)
    {
        $registrationType = $request->input('registration_type', 'basic');
        $isVerified = $registrationType === 'verified';

        // Base validation rules
        $rules = [
            'business_name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'phone' => ['required', 'string', 'regex:/^\+63[0-9]{10}$/'],
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'region' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'barangay' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'toy_category_ids' => 'required|array|min:1|max:3',
            'toy_category_ids.*' => 'exists:categories,id',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'facial_verification' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'bank_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
        ];

        // Add verified shop requirements
        if ($isVerified) {
            $rules['business_permit'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['bir_certificate'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120';
            $rules['product_sample'] = 'required|file|mimes:jpg,jpeg,png|max:5120';
        }

        $request->validate($rules);

        // Create seller
        $seller = Seller::create([
            'user_id' => Auth::id(),
            'business_name' => $request->business_name,
            'business_slug' => Str::slug($request->business_name) . '-' . Auth::id(),
            'description' => $request->description,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'region' => $request->region,
            'city' => $request->city,
            'barangay' => $request->barangay,
            'province' => $request->province,
            'postal_code' => $request->postal_code,
            'facebook_url' => $request->facebook_url,
            'instagram_url' => $request->instagram_url,
            'tiktok_url' => $request->tiktok_url,
            'website_url' => $request->website_url,
            'toy_category_ids' => $request->toy_category_ids,
            'verification_status' => 'pending',
            'is_verified_shop' => $isVerified, // Store verification type
        ]);

        // Upload Primary ID document (required for all)
        $idPath = $request->file('id_document')->store('seller_documents/' . $seller->id, 'public');
        \App\Models\SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => 'id',
            'document_path' => $idPath,
            'status' => 'pending',
        ]);

        // Upload Facial Verification (required for all)
        $facialPath = $request->file('facial_verification')->store('seller_documents/' . $seller->id, 'public');
        \App\Models\SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => 'facial_verification',
            'document_path' => $facialPath,
            'status' => 'pending',
        ]);

        // Upload Bank document (required for all)
        $bankPath = $request->file('bank_document')->store('seller_documents/' . $seller->id, 'public');
        \App\Models\SellerDocument::create([
            'seller_id' => $seller->id,
            'document_type' => 'bank_statement',
            'document_path' => $bankPath,
            'status' => 'pending',
        ]);

        // Upload verified trusted seller documents (only if verified registration)
        if ($isVerified) {
            $businessPermitPath = $request->file('business_permit')->store('seller_documents/' . $seller->id, 'public');
            \App\Models\SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'business_permit',
                'document_path' => $businessPermitPath,
                'status' => 'pending',
            ]);

            $birCertPath = $request->file('bir_certificate')->store('seller_documents/' . $seller->id, 'public');
            \App\Models\SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'bir_certificate',
                'document_path' => $birCertPath,
                'status' => 'pending',
            ]);

            $productSamplePath = $request->file('product_sample')->store('seller_documents/' . $seller->id, 'public');
            \App\Models\SellerDocument::create([
                'seller_id' => $seller->id,
                'document_type' => 'product_sample',
                'document_path' => $productSamplePath,
                'status' => 'pending',
            ]);
        }

        // Update user role
        Auth::user()->update(['role' => 'seller']);

        $message = $isVerified 
            ? 'Verified Trusted Toyshop registration submitted successfully! Your account will be reviewed by our admin team.'
            : 'Local Business Toyshop registration submitted successfully! Your account will be reviewed by our admin team.';

        return redirect()->route('seller.dashboard')
            ->with('success', $message);
    }
}
