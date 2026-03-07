<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    /**
     * Auction index (live listings). Requires active membership.
     */
    public function index(Request $request)
    {
        if (! $request->user()->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Join a membership plan to access live auctions.');
        }

        return view('auction.index');
    }

    /**
     * Become seller flow: VIP gets Individual/Business choice; Basic/Pro get upgrade prompt.
     */
    public function becomeSeller(Request $request)
    {
        if (! $request->user()->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Join a membership plan to become an auction seller.');
        }

        $plan = $request->user()->currentPlan();
        $canRegisterSeller = $plan && $plan->can_register_individual_seller;

        if (! $canRegisterSeller) {
            return view('auction.become-seller', [
                'requiresUpgrade' => true,
                'currentPlan' => $plan,
            ]);
        }

        return view('auction.become-seller', [
            'requiresUpgrade' => false,
            'currentPlan' => $plan,
        ]);
    }
}
