<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerDocument;
use App\Models\AuctionSellerVerification;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'membership']);
    }

    public function index()
    {
        $user = Auth::user();

        if (! $user->hasPlan('vip')) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need a VIP membership to register as an auction seller.');
        }

        $verification = $user->auctionSellerVerification;

        return view('auctions.seller-verification.index', compact('verification'));
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user->hasPlan('vip')) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need a VIP membership to register as an auction seller.');
        }

        $verification = $user->auctionSellerVerification;
        if ($verification && $verification->isApproved()) {
            return redirect()->route('auctions.seller.index')->with('info', 'You are already verified.');
        }
        if ($verification && $verification->isPending()) {
            return redirect()->route('auctions.verification.status')->with('info', 'Your verification is pending.');
        }

        $verifiedSellers = $user->seller && $user->seller->is_verified_shop ? [$user->seller] : collect();

        return view('auctions.seller-verification.create', compact('verifiedSellers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasPlan('vip')) {
            return redirect()->route('membership.index')->with('error', 'VIP membership required.');
        }

        $sellerType = $request->input('seller_type', 'individual');

        if ($sellerType === 'business') {
            return $this->storeBusiness($request, $user);
        }

        return $this->storeIndividual($request, $user);
    }

    protected function storeIndividual(Request $request, $user)
    {
        $request->validate([
            'gov_id_1' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'gov_id_2' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'facial' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'bank_statement' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $verification = AuctionSellerVerification::create([
            'user_id' => $user->id,
            'seller_id' => null,
            'type' => 'individual',
            'verification_status' => 'pending',
        ]);

        $docs = [
            ['document_type' => 'govt_id_1', 'file' => $request->file('gov_id_1')],
            ['document_type' => 'govt_id_2', 'file' => $request->file('gov_id_2')],
            ['document_type' => 'facial_recognition', 'file' => $request->file('facial')],
            ['document_type' => 'bank_statement', 'file' => $request->file('bank_statement')],
        ];

        foreach ($docs as $doc) {
            $path = $doc['file']->store('auction-seller-docs/' . $verification->id, 'public');
            AuctionSellerDocument::create([
                'verification_id' => $verification->id,
                'document_type' => $doc['document_type'],
                'document_path' => $path,
            ]);
        }

        return redirect()->route('auctions.verification.status')->with('success', 'Verification submitted. We will review shortly.');
    }

    protected function storeBusiness(Request $request, $user)
    {
        $request->validate(['seller_id' => 'required|exists:sellers,id']);

        $seller = Seller::findOrFail($request->seller_id);

        if ($seller->user_id !== $user->id) {
            return back()->with('error', 'Invalid seller.');
        }

        if (! $seller->is_verified_shop) {
            return back()->with('error', 'Your Toyshop must be Fully Verified Trusted Shop to register as a Business Auction Seller.');
        }

        AuctionSellerVerification::create([
            'user_id' => $user->id,
            'seller_id' => $seller->id,
            'type' => 'business',
            'verification_status' => 'pending',
        ]);

        return redirect()->route('auctions.verification.status')->with('success', 'Verification submitted. We will review shortly.');
    }

    public function status()
    {
        $verification = Auth::user()->auctionSellerVerification;

        if (! $verification) {
            return redirect()->route('auctions.verification.index');
        }

        return view('auctions.seller-verification.status', compact('verification'));
    }
}
