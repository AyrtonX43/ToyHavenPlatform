<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public const TIERS = [
        '24h' => ['label' => 'Featured for 24 Hours', 'price' => 99, 'hours' => 24],
        '3d' => ['label' => 'Featured for 3 Days', 'price' => 249, 'hours' => 72],
        '7d' => ['label' => 'Featured for 7 Days', 'price' => 499, 'hours' => 168],
    ];

    public function __construct(
        protected PayMongoService $payMongo
    ) {}

    public function show(Auction $auction)
    {
        $user = Auth::user();

        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if ($auction->status !== 'live') {
            return back()->with('error', 'Only live auctions can be promoted.');
        }

        $tiers = self::TIERS;

        return view('auctions.seller.promote', compact('auction', 'tiers'));
    }

    public function store(Request $request, Auction $auction)
    {
        $user = Auth::user();

        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'tier' => 'required|in:24h,3d,7d',
        ]);

        $tier = self::TIERS[$request->tier];

        // Check if user has wallet balance
        $wallet = $user->getOrCreateWallet();
        if ($wallet->balance >= $tier['price']) {
            $wallet->debit(
                $tier['price'],
                'auction_payment',
                "Auction promotion: {$tier['label']} for #{$auction->id}",
                $auction
            );

            $promotedUntil = $auction->isCurrentlyPromoted()
                ? $auction->promoted_until->addHours($tier['hours'])
                : now()->addHours($tier['hours']);

            $auction->update([
                'is_promoted' => true,
                'promoted_until' => $promotedUntil,
            ]);

            return redirect()->route('auctions.seller.index')
                ->with('success', "Auction promoted! {$tier['label']} - paid from wallet.");
        }

        // Otherwise create PayMongo intent
        $intent = $this->payMongo->createPaymentIntent(
            (float) $tier['price'],
            'PHP',
            [
                'auction_id' => (string) $auction->id,
                'promotion_tier' => $request->tier,
                'user_id' => (string) $user->id,
            ]
        );

        if (! $intent) {
            return back()->with('error', 'Could not initialize payment.');
        }

        // Store promotion intent in session for return handling
        session([
            'auction_promotion' => [
                'auction_id' => $auction->id,
                'tier' => $request->tier,
                'payment_intent_id' => $intent['id'],
            ],
        ]);

        return redirect()->route('auctions.seller.index')
            ->with('info', "Promotion payment created. Complete payment to activate {$tier['label']}.");
    }

    public function activate(Request $request, Auction $auction)
    {
        $promotionData = session('auction_promotion');
        if (! $promotionData || $promotionData['auction_id'] !== $auction->id) {
            return back()->with('error', 'Invalid promotion session.');
        }

        $intent = $this->payMongo->getPaymentIntent($promotionData['payment_intent_id']);
        $status = $intent['attributes']['status'] ?? 'unknown';

        if ($status === 'succeeded') {
            $tier = self::TIERS[$promotionData['tier']];

            $promotedUntil = $auction->isCurrentlyPromoted()
                ? $auction->promoted_until->addHours($tier['hours'])
                : now()->addHours($tier['hours']);

            $auction->update([
                'is_promoted' => true,
                'promoted_until' => $promotedUntil,
            ]);

            session()->forget('auction_promotion');

            return redirect()->route('auctions.seller.index')
                ->with('success', "Auction promoted! {$tier['label']}");
        }

        return back()->with('error', 'Payment not completed.');
    }
}
