<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionListingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasAuctionModeratePermission()) {
                abort(403, 'You do not have permission to moderate auctions.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Auction::with(['user', 'category'])
            ->whereIn('status', ['pending_approval', 'active', 'ended'])
            ->orderByDesc('updated_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['pending_approval', 'active', 'ended'])) {
            $query->where('status', $status);
        }

        $listings = $query->paginate(20);

        return view('moderator.auction-listings.index', compact('listings'));
    }

    public function show(Auction $listing)
    {
        $listing->load(['user', 'category', 'images']);

        return view('moderator.auction-listings.show', compact('listing'));
    }

    public function approve(Request $request, Auction $listing)
    {
        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'This listing is not pending approval.');
        }

        $watcherCount = $listing->watchersCount();
        $minRequired = $listing->min_watchers_to_approve;
        if ($minRequired !== null && $minRequired > 0 && $watcherCount < $minRequired && ! $request->boolean('override_min_watchers')) {
            return back()->with('error', "Minimum watchers not met. Has {$watcherCount} watchers, requires {$minRequired}. Use the override option to approve anyway.");
        }

        $durationHours = $listing->duration_hours ?? 24;
        $startAt = now();
        $endAt = now()->addHours($durationHours);

        $listing->update([
            'status' => 'active',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Auction listing approved and is now live.');
    }

    public function reject(Request $request, Auction $listing)
    {
        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'This listing is not pending approval.');
        }

        $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        $listing->update([
            'status' => 'draft',
            'rejection_reason' => $request->feedback,
        ]);

        return back()->with('success', 'Auction listing rejected. The seller has been notified and can resubmit with corrections.');
    }
}
