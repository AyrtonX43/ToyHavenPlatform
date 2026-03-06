<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerVerification;
use App\Notifications\AuctionSellerApprovedNotification;
use App\Notifications\AuctionSellerRejectedNotification;
use Illuminate\Http\Request;

class AuctionVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionSellerVerification::with(['user', 'seller', 'documents'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $verifications = $query->paginate(15);

        return view('admin.auctions.verifications.index', compact('verifications'));
    }

    public function show(AuctionSellerVerification $verification)
    {
        $verification->load(['user', 'seller', 'documents']);

        return view('admin.auctions.verifications.show', compact('verification'));
    }

    public function approve(AuctionSellerVerification $verification)
    {
        $verification->update([
            'verification_status' => 'approved',
            'rejection_reason' => null,
        ]);

        $verification->user->notify(new AuctionSellerApprovedNotification($verification));

        return redirect()->route('admin.auction-verifications.index')
            ->with('success', 'Verification approved. User has been notified.');
    }

    public function reject(Request $request, AuctionSellerVerification $verification)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $verification->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        $verification->user->notify(new AuctionSellerRejectedNotification($verification));

        return redirect()->route('admin.auction-verifications.index')
            ->with('success', 'Verification rejected. User has been notified.');
    }

    public function requestResubmission(Request $request, AuctionSellerVerification $verification)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $verification->update([
            'verification_status' => 'requires_resubmission',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.auction-verifications.index')
            ->with('success', 'Resubmission requested.');
    }
}
