<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionSellerVerification::with(['user', 'seller']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('user', fn ($sql) => $sql->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        $verifications = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.verifications.index', compact('verifications'));
    }

    public function show(AuctionSellerVerification $verification)
    {
        $verification->load(['user', 'seller', 'documents', 'verifiedByUser']);

        return view('admin.auctions.verifications.show', compact('verification'));
    }

    public function approve(AuctionSellerVerification $verification)
    {
        if ($verification->status !== 'pending') {
            return back()->with('error', 'Verification is not pending.');
        }

        $verification->update([
            'status' => 'approved',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
            'rejection_reason' => null,
        ]);

        $verification->documents()->update(['status' => 'approved']);

        return back()->with('success', 'Auction seller verification approved.');
    }

    public function reject(Request $request, AuctionSellerVerification $verification)
    {
        if ($verification->status !== 'pending') {
            return back()->with('error', 'Verification is not pending.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Auction seller verification rejected.');
    }

    public function requestResubmission(Request $request, AuctionSellerVerification $verification)
    {
        if ($verification->status !== 'pending') {
            return back()->with('error', 'Verification is not pending.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $verification->update([
            'status' => 'requires_resubmission',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Resubmission requested.');
    }
}
