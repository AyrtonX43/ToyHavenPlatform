<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerDocument;
use App\Models\AuctionSellerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $plan = $user->currentPlan();

        if (! $plan || (strtolower($plan->slug) !== 'vip' && ! $plan->canCreateAuction())) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need VIP membership to list auctions.');
        }

        $profile = $user->auctionSellerProfile;

        if ($profile?->isApproved()) {
            return redirect()->route('auctions.seller.index')
                ->with('info', 'You are already approved to list auctions.');
        }

        return view('auctions.seller-profile.index', compact('profile', 'plan'));
    }

    public function create()
    {
        $user = Auth::user();
        $plan = $user->currentPlan();

        if (! $plan || (strtolower($plan->slug) !== 'vip' && ! $plan->canCreateAuction())) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need VIP membership to list auctions.');
        }

        $profile = $user->auctionSellerProfile;

        if ($profile && $profile->status === 'pending') {
            return redirect()->route('auctions.seller-profile.status')
                ->with('info', 'Your application is pending review.');
        }

        return view('auctions.seller-profile.create', [
            'type' => request()->get('type', 'individual'),
            'plan' => $plan,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $plan = $user->currentPlan();

        if (! $plan || (strtolower($plan->slug) !== 'vip' && ! $plan->canCreateAuction())) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need VIP membership to list auctions.');
        }

        $sellerType = $request->input('seller_type', 'individual');

        $rules = [
            'seller_type' => 'required|in:individual,business',
            'paypal_email' => 'required|email',
        ];

        if ($sellerType === 'business') {
            $rules['business_name'] = 'required|string|max:255';
            $rules['bir_certificate'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['official_receipt_sample'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
        }

        $request->validate($rules);

        $profile = AuctionSellerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'seller_type' => $sellerType,
                'business_name' => $sellerType === 'business' ? $request->business_name : null,
                'paypal_email' => $request->paypal_email,
                'status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        if ($sellerType === 'business') {
            $storagePath = 'auction_seller_docs/' . $profile->id;

            foreach (['bir_certificate', 'official_receipt_sample'] as $docType) {
                if ($request->hasFile($docType)) {
                    $path = $request->file($docType)->store($storagePath, 'public');
                    AuctionSellerDocument::create([
                        'auction_seller_profile_id' => $profile->id,
                        'document_type' => $docType,
                        'document_path' => $path,
                        'status' => 'pending',
                    ]);
                }
            }
        }

        return redirect()->route('auctions.seller-profile.status')
            ->with('success', 'Application submitted. We will review it shortly.');
    }

    public function status()
    {
        $user = Auth::user();
        $profile = $user->auctionSellerProfile;

        if (! $profile) {
            return redirect()->route('auctions.seller-profile.index');
        }

        return view('auctions.seller-profile.status', compact('profile'));
    }
}
