<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerDocument;
use App\Models\AuctionSellerVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SellerRegistrationController extends Controller
{
    public function showIndividualForm()
    {
        $user = Auth::user();
        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')->with('error', 'Membership required.');
        }
        if ($user->hasApprovedBusinessAuctionSeller()) {
            return redirect()->route('auction.seller.dashboard')->with('info', 'You are already registered as a business seller. Individual registration is not needed.');
        }
        if ($user->hasApprovedIndividualAuctionSeller()) {
            return redirect()->route('auction.seller.dashboard')->with('info', 'You are already registered as an individual seller.');
        }
        $plan = $user->currentPlan();
        if (! $plan || ! $plan->can_register_individual_seller) {
            return redirect()->route('membership.upgrade', 'vip')->with('info', 'Individual auction seller registration requires VIP membership.');
        }

        return view('auction.seller-registration.individual');
    }

    public function storeIndividual(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')->with('error', 'Membership required.');
        }
        if ($user->hasApprovedBusinessAuctionSeller()) {
            return redirect()->route('auction.seller.dashboard')->with('info', 'You are already registered as a business seller.');
        }
        if ($user->hasApprovedIndividualAuctionSeller()) {
            return redirect()->route('auction.seller.dashboard')->with('info', 'You are already registered as an individual seller.');
        }
        $plan = $user->currentPlan();
        if (! $plan || ! $plan->can_register_individual_seller) {
            return redirect()->route('membership.upgrade', 'vip')->with('info', 'Individual auction seller registration requires VIP membership.');
        }

        $request->validate([
            'government_id_1' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'government_id_2' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'facial_verification' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'bank_statement' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            $verification = DB::transaction(function () use ($request, $user) {
                $verification = AuctionSellerVerification::create([
                    'user_id' => $user->id,
                    'type' => 'individual',
                    'verification_status' => 'pending',
                ]);

                $docs = [
                    'government_id_1' => $request->file('government_id_1'),
                    'government_id_2' => $request->file('government_id_2'),
                    'facial_verification' => $request->file('facial_verification'),
                    'bank_statement' => $request->file('bank_statement'),
                ];
                foreach ($docs as $type => $file) {
                    $path = $file->store('auction_seller_documents/' . $verification->id, 'public');
                    AuctionSellerDocument::create([
                        'verification_id' => $verification->id,
                        'document_type' => $type,
                        'document_path' => $path,
                    ]);
                }

                return $verification;
            });
        } catch (\Throwable $e) {
            Log::error('Auction seller individual registration failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Registration could not be completed. Please try again or contact support if the problem persists.');
        }

        return redirect()->route('auction.index')
            ->with('success', 'Your individual auction seller registration has been submitted. You will receive an email and notification once an admin reviews your application.');
    }

    public function showBusinessForm()
    {
        $user = Auth::user();
        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')->with('error', 'Membership required.');
        }
        if ($user->hasApprovedBusinessAuctionSeller()) {
            return redirect()->route('auction.index')->with('info', 'You are already registered as a business auction seller.');
        }
        $plan = $user->currentPlan();
        if (! $plan || ! $plan->can_register_business_seller) {
            return redirect()->route('membership.upgrade', 'vip')->with('info', 'Business auction seller registration requires VIP membership.');
        }

        $defaultAddress = $user->defaultAddress;
        $phoneDisplay = $user->phone ? (strpos($user->phone, '+63') === 0 ? substr($user->phone, 3) : $user->phone) : '';
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

        return view('auction.seller-registration.business', compact('prefilledData'));
    }

    public function storeBusiness(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')->with('error', 'Membership required.');
        }
        if ($user->hasApprovedBusinessAuctionSeller()) {
            return redirect()->route('auction.index')->with('info', 'You are already registered as a business auction seller.');
        }
        $plan = $user->currentPlan();
        if (! $plan || ! $plan->can_register_business_seller) {
            return redirect()->route('membership.upgrade', 'vip')->with('info', 'Business auction seller registration requires VIP membership.');
        }

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
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'facial_verification' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'bank_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'business_permit' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'bir_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'product_sample' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ];
        $request->validate($rules);

        $normalize = function_exists('normalizePhilippineText')
            ? fn ($t) => normalizePhilippineText($t)
            : fn ($t) => is_string($t) ? trim((string) $t) : $t;
        $businessInfo = [
            'business_name' => $normalize($request->business_name),
            'description' => $normalize($request->description),
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $normalize($request->address),
            'region' => $normalize($request->region),
            'city' => $normalize($request->city),
            'barangay' => $normalize($request->barangay),
            'province' => $normalize($request->province),
            'postal_code' => $request->postal_code,
        ];

        $verificationData = [
            'user_id' => $user->id,
            'type' => 'business',
            'verification_status' => 'pending',
        ];
        if (Schema::hasColumn('auction_seller_verifications', 'business_info')) {
            $verificationData['business_info'] = $businessInfo;
        }

        try {
            $verification = DB::transaction(function () use ($request, $user, $verificationData) {
                $verification = AuctionSellerVerification::create($verificationData);

                $docs = [
                    'id' => $request->file('id_document'),
                    'facial_verification' => $request->file('facial_verification'),
                    'bank_statement' => $request->file('bank_document'),
                    'business_permit' => $request->file('business_permit'),
                    'bir_certificate' => $request->file('bir_certificate'),
                    'product_sample' => $request->file('product_sample'),
                ];
                foreach ($docs as $type => $file) {
                    $path = $file->store('auction_seller_documents/' . $verification->id, 'public');
                    AuctionSellerDocument::create([
                        'verification_id' => $verification->id,
                        'document_type' => $type,
                        'document_path' => $path,
                    ]);
                }

                return $verification;
            });
        } catch (\Throwable $e) {
            Log::error('Auction seller business registration failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Registration could not be completed. Please try again or contact support if the problem persists.');
        }

        return redirect()->route('auction.index')
            ->with('success', 'Your business auction seller registration has been submitted. You will receive an email and notification once an admin reviews your application.');
    }
}
