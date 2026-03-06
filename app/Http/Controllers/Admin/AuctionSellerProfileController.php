<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerProfile;
use Illuminate\Http\Request;

class AuctionSellerProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionSellerProfile::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $profiles = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auction-seller-profiles.index', compact('profiles'));
    }

    public function show(AuctionSellerProfile $auctionSellerProfile)
    {
        $auctionSellerProfile->load(['user', 'documents']);

        return view('admin.auction-seller-profiles.show', compact('auctionSellerProfile'));
    }

    public function approve(AuctionSellerProfile $auctionSellerProfile)
    {
        if ($auctionSellerProfile->status !== 'pending') {
            return back()->with('error', 'Only pending applications can be approved.');
        }

        $auctionSellerProfile->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Auction seller profile approved.');
    }

    public function reject(Request $request, AuctionSellerProfile $auctionSellerProfile)
    {
        if ($auctionSellerProfile->status !== 'pending') {
            return back()->with('error', 'Only pending applications can be rejected.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $auctionSellerProfile->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Auction seller profile rejected.');
    }

    public function suspend(AuctionSellerProfile $auctionSellerProfile)
    {
        $auctionSellerProfile->update(['status' => 'suspended']);

        return back()->with('success', 'Auction seller suspended.');
    }

    public function activate(AuctionSellerProfile $auctionSellerProfile)
    {
        $auctionSellerProfile->update(['status' => 'approved']);

        return back()->with('success', 'Auction seller activated.');
    }
}
