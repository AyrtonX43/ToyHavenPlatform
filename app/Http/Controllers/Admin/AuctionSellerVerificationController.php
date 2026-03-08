<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerVerification;
use App\Notifications\AuctionSellerApprovedNotification;
use App\Notifications\AuctionSellerRejectedNotification;
use Illuminate\Http\Request;

class AuctionSellerVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionSellerVerification::with(['user', 'documents'])
            ->orderByDesc('created_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('verification_status', $status);
        }

        $verifications = $query->paginate(20);

        return view('admin.auction-seller-verifications.index', compact('verifications'));
    }

    public function show(AuctionSellerVerification $verification)
    {
        $verification->load(['user', 'documents']);

        return view('admin.auction-seller-verifications.show', compact('verification'));
    }

    public function approve(AuctionSellerVerification $verification)
    {
        if ($verification->verification_status !== 'pending') {
            return back()->with('error', 'This verification has already been processed.');
        }

        $verification->update(['verification_status' => 'approved', 'rejection_reason' => null]);

        $businessName = $verification->type === 'business' && $verification->business_info
            ? ($verification->business_info['business_name'] ?? null)
            : null;

        try {
            $verification->user->notify(new AuctionSellerApprovedNotification($verification->type, $businessName));
        } catch (\Exception $e) {
            \Log::error('Failed to send auction seller approval notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Auction seller registration approved. The user has been notified via email and in-app notification.');
    }

    public function reject(Request $request, AuctionSellerVerification $verification)
    {
        if ($verification->verification_status !== 'pending') {
            return back()->with('error', 'This verification has already been processed.');
        }

        $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        $verification->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $request->feedback,
        ]);

        $businessName = $verification->type === 'business' && $verification->business_info
            ? ($verification->business_info['business_name'] ?? null)
            : null;

        try {
            $verification->user->notify(new AuctionSellerRejectedNotification($verification->type, $request->feedback, $businessName));
        } catch (\Exception $e) {
            \Log::error('Failed to send auction seller rejection notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Auction seller registration rejected. The user has been notified via email and in-app notification with your feedback.');
    }
}
