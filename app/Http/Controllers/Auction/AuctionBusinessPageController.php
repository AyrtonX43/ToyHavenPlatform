<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionSellerVerification;
use Illuminate\Http\Request;

class AuctionBusinessPageController extends Controller
{
    /**
     * Public business auction seller page - shows seller profile and their auctions.
     */
    public function show(string $slug)
    {
        $verification = AuctionSellerVerification::where('auction_seller_slug', $slug)
            ->where('status', 'approved')
            ->where('is_suspended', false)
            ->with('user')
            ->firstOrFail();

        $displayName = $verification->getDisplayName();

        $liveAuctions = Auction::where('user_id', $verification->user_id)
            ->live()
            ->with(['category', 'images'])
            ->orderBy('end_at')
            ->limit(12)
            ->get();

        $endedAuctions = Auction::where('user_id', $verification->user_id)
            ->whereIn('status', ['ended'])
            ->with(['category', 'images', 'winner'])
            ->orderByDesc('end_at')
            ->limit(12)
            ->get();

        $stats = [
            'total_auctions' => Auction::where('user_id', $verification->user_id)->count(),
            'live_count' => Auction::where('user_id', $verification->user_id)->live()->count(),
            'ended_count' => Auction::where('user_id', $verification->user_id)->where('status', 'ended')->count(),
        ];

        return view('auctions.business.show', [
            'verification' => $verification,
            'displayName' => $displayName,
            'liveAuctions' => $liveAuctions,
            'endedAuctions' => $endedAuctions,
            'stats' => $stats,
        ]);
    }
}
