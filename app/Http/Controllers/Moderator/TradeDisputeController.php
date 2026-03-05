<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorAction;
use App\Models\TradeDispute;
use App\Services\TradeDisputeService;
use Illuminate\Http\Request;

class TradeDisputeController extends Controller
{
    public function __construct(
        protected TradeDisputeService $disputeService
    ) {
        //
    }

    public function index(Request $request)
    {
        $query = TradeDispute::with(['trade.tradeListing', 'reporter', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->orderByDesc('created_at')->paginate(20);
        return view('moderator.trade-disputes.index', compact('disputes'));
    }

    public function show($id)
    {
        $dispute = TradeDispute::with([
            'trade.tradeListing.images',
            'trade.initiator',
            'trade.participant',
            'trade.items',
            'reporter',
            'assignedTo',
        ])->findOrFail($id);

        return view('moderator.trade-disputes.show', compact('dispute'));
    }

    public function assign($id)
    {
        $dispute = TradeDispute::findOrFail($id);

        if ($dispute->assigned_to && $dispute->assigned_to !== auth()->id()) {
            return back()->with('error', 'This dispute is already assigned to another moderator.');
        }

        $dispute->update([
            'assigned_to' => auth()->id(),
            'status' => 'investigating',
        ]);

        ModeratorAction::log(auth()->id(), 'trade_dispute_assigned', $dispute, 'Trade dispute assigned to self', [
            'dispute_id' => $dispute->id,
        ]);

        return back()->with('success', 'Dispute assigned to you.');
    }

    public function resolve(Request $request, $id)
    {
        $dispute = TradeDispute::findOrFail($id);

        $validated = $request->validate([
            'resolution' => 'required|in:completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->disputeService->resolve($dispute, $validated['resolution'], $validated['notes'] ?? null);

        ModeratorAction::log(auth()->id(), 'trade_dispute_resolved', $dispute, 'Trade dispute resolved', [
            'dispute_id' => $dispute->id,
            'resolution' => $validated['resolution'],
        ]);

        return back()->with('success', 'Dispute resolved successfully.');
    }
}
