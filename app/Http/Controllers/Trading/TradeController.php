<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\TradeDispute;
use App\Notifications\TradeCancelRequestNotification;
use App\Services\TradeMeetupService;
use App\Services\TradeDisputeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradeController extends Controller
{
    public function __construct(
        protected TradeMeetupService $meetupService,
        protected TradeDisputeService $disputeService
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $trades = Trade::where('initiator_id', Auth::id())
            ->orWhere('participant_id', Auth::id())
            ->with(['tradeListing.images', 'initiator', 'participant'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('trading.trades.index', compact('trades'));
    }

    public function show($id)
    {
        $trade = Trade::with([
            'tradeListing.images',
            'tradeListing.user',
            'tradeOffer',
            'initiator',
            'participant',
            'items',
            'conversation',
        ])->findOrFail($id);

        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }

        return view('trading.trades.show', compact('trade'));
    }

    public function scheduleMeetup(Request $request, $id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }
        if (!in_array($trade->status, ['pending_meetup', 'meetup_scheduled'])) {
            return back()->with('error', 'Meetup cannot be scheduled for this trade.');
        }

        $validated = $request->validate([
            'meetup_location_address' => 'required|string|max:500',
            'meetup_location_lat' => 'nullable|numeric',
            'meetup_location_lng' => 'nullable|numeric',
            'meetup_scheduled_at' => 'nullable|date',
        ]);

        $this->meetupService->scheduleMeetup($trade, $validated);
        return back()->with('success', 'Meetup location and time updated.');
    }

    public function confirmMeetup($id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }
        if ($trade->status !== 'meetup_scheduled') {
            return back()->with('error', 'Meetup must be scheduled first.');
        }

        $this->meetupService->confirmMeetup($trade);
        return back()->with('success', 'Meetup marked as completed. Both parties should confirm in the chat.');
    }

    public function complete($id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }
        if (!in_array($trade->status, ['meetup_scheduled', 'meetup_completed'])) {
            return back()->with('error', 'Cannot complete this trade.');
        }

        $this->meetupService->complete($trade);
        return redirect()->route('trading.trades.index')
            ->with('success', 'Trade completed.');
    }

    /**
     * Mutual cancel: first call requests cancel; when both have requested, trade is cancelled and conversation locked.
     */
    public function cancel(Request $request, $id)
    {
        $trade = Trade::with('conversation')->findOrFail($id);
        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }
        if (in_array($trade->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This trade cannot be cancelled.');
        }

        $userRequested = ($trade->isInitiator(Auth::id()) && $trade->initiator_cancel_requested_at)
            || ($trade->isParticipant(Auth::id()) && $trade->participant_cancel_requested_at);
        $trade->requestCancel(Auth::id());

        if ($trade->fresh()->bothRequestedCancel()) {
            $this->meetupService->cancel($trade);
            if ($trade->conversation) {
                $trade->conversation->update(['is_locked' => true]);
            }
            return redirect()->route('trading.trades.index')
                ->with('success', 'Trade cancelled. Both parties confirmed.');
        }

        if (!$userRequested) {
            $otherUser = $trade->getOtherParty(Auth::id());
            try {
                $otherUser?->notify(new TradeCancelRequestNotification($trade->fresh()));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Trade cancel request notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Cancel requested. The other party has been notified. They have 24 hours to respond. If they accept or do not respond, the trade will be cancelled.');
    }

    public function disputeForm($id)
    {
        $trade = Trade::with(['tradeListing', 'dispute'])->findOrFail($id);
        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }
        if ($trade->status !== 'meetup_scheduled' && $trade->status !== 'meetup_completed') {
            return back()->with('error', 'Disputes can only be opened for active meetups.');
        }
        if ($trade->dispute) {
            return redirect()->route('trading.trades.show', $id)->with('info', 'A dispute is already open.');
        }

        return view('trading.trades.dispute', compact('trade'));
    }

    public function dispute(Request $request, $id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->initiator_id !== Auth::id() && $trade->participant_id !== Auth::id()) {
            abort(403);
        }
        if ($trade->dispute) {
            return back()->with('error', 'A dispute already exists.');
        }

        $validated = $request->validate([
            'type' => 'required|in:no_show,wrong_item,damaged,other',
            'description' => 'required|string|max:2000',
            'evidence_images' => 'nullable|array|max:5',
            'evidence_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $paths = [];
        if ($request->hasFile('evidence_images')) {
            foreach ($request->file('evidence_images') as $file) {
                $paths[] = $file->store('trade-disputes/' . $trade->id, 'public');
            }
        }
        $validated['evidence_images'] = $paths;

        $this->disputeService->create($trade, $validated);
        return redirect()->route('trading.trades.show', $id)
            ->with('success', 'Dispute submitted. A moderator will review it.');
    }
}
