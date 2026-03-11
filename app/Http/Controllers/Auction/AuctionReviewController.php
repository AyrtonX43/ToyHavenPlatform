<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use App\Models\AuctionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AuctionReviewController extends Controller
{
    public function store(Request $request, AuctionPayment $payment)
    {
        $user = Auth::user();

        if ($payment->winner_id !== $user->id) {
            abort(403);
        }

        if ($payment->delivery_status !== 'delivered' && $payment->delivery_status !== 'confirmed') {
            return back()->with('error', 'You must confirm delivery before leaving a review.');
        }

        if (! Schema::hasTable('auction_reviews')) {
            return back()->with('error', 'Reviews are not available at this time.');
        }

        if (AuctionReview::where('auction_payment_id', $payment->id)->exists()) {
            return back()->with('error', 'You have already reviewed this auction.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos') && Schema::hasColumn('auction_reviews', 'photos')) {
            try {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('auction_reviews/' . $payment->id, 'public');
                    if ($path) {
                        $photoPaths[] = $path;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Auction review photo upload failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }
        }

        try {
            $payment->load('auction');
            $sellerUserId = $payment->auction?->user_id ?? 0;
            if (! $sellerUserId) {
                return back()->with('error', 'Could not determine seller.');
            }

            $data = [
                'auction_payment_id' => $payment->id,
                'winner_id' => $user->id,
                'auction_id' => $payment->auction_id,
                'seller_user_id' => $sellerUserId,
                'rating' => $request->rating,
                'feedback' => $request->feedback ?: null,
                'delivery_confirmed_at' => $payment->confirmed_at ?? now(),
            ];
            if (Schema::hasColumn('auction_reviews', 'photos')) {
                $data['photos'] = ! empty($photoPaths) ? $photoPaths : null;
            }

            AuctionReview::create($data);

            return back()->with('success', 'Thank you for your review!');
        } catch (\Throwable $e) {
            Log::error('Auction review store failed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Could not save your review. Please try again.');
        }
    }
}
