<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerDocument;
use App\Models\AuctionSellerVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SellerRegistrationController extends Controller
{
    public function showIndividualForm()
    {
        $user = Auth::user();
        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')->with('error', 'Membership required.');
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

        return redirect()->route('auction.index')
            ->with('success', 'Your individual auction seller registration has been submitted. You will receive an email and notification once an admin reviews your application.');
    }

    public function showBusinessForm()
    {
        $user = Auth::user();
        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')->with('error', 'Membership required.');
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

        $businessInfo = [
            'business_name' => normalizePhilippineText($request->business_name),
            'description' => normalizePhilippineText($request->description),
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => normalizePhilippineText($request->address),
            'region' => normalizePhilippineText($request->region),
            'city' => normalizePhilippineText($request->city),
            'barangay' => normalizePhilippineText($request->barangay),
            'province' => normalizePhilippineText($request->province),
            'postal_code' => $request->postal_code,
        ];

        $verification = DB::transaction(function () use ($request, $user, $businessInfo) {
            $verification = AuctionSellerVerification::create([
                'user_id' => $user->id,
                'type' => 'business',
                'business_info' => $businessInfo,
                'verification_status' => 'pending',
            ]);

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

        return redirect()->route('auction.index')
            ->with('success', 'Your business auction seller registration has been submitted. You will receive an email and notification once an admin reviews your application.');
    }
}
