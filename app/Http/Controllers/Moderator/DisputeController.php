<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\OrderDispute;
use App\Models\OrderDisputeMessage;
use App\Notifications\DisputeMessageNotification;
use App\Notifications\DisputeResolvedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderDispute::with(['order', 'user', 'seller', 'moderator']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('dispute_number', 'like', "%{$search}%")
                  ->orWhereHas('order', function($q) use ($search) {
                      $q->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('moderator.disputes.index', compact('disputes'));
    }

    public function show($id)
    {
        $dispute = OrderDispute::with(['order.items.product', 'user', 'seller', 'moderator', 'messages.user'])
            ->findOrFail($id);

        if (!$dispute->moderator_id) {
            $dispute->update(['moderator_id' => Auth::id()]);
        }

        return view('moderator.disputes.show', compact('dispute'));
    }

    public function updateStatus(Request $request, $id)
    {
        $dispute = OrderDispute::findOrFail($id);

        $request->validate([
            'status' => 'required|in:open,investigating,resolved,closed',
        ]);

        $dispute->update([
            'status' => $request->status,
            'moderator_id' => $dispute->moderator_id ?? Auth::id(),
        ]);

        return back()->with('success', 'Dispute status updated successfully.');
    }

    public function resolve(Request $request, $id)
    {
        $dispute = OrderDispute::findOrFail($id);

        if (!$dispute->canBeResolved()) {
            return back()->with('error', 'This dispute cannot be resolved.');
        }

        $request->validate([
            'resolution' => 'required|in:refund,replacement,partial_refund,no_action',
            'resolution_notes' => 'required|string|min:10|max:1000',
        ]);

        $dispute->update([
            'status' => 'resolved',
            'resolution' => $request->resolution,
            'resolution_notes' => $request->resolution_notes,
            'resolved_at' => now(),
            'moderator_id' => $dispute->moderator_id ?? Auth::id(),
        ]);

        if ($request->resolution === 'refund') {
            $dispute->order->update(['payment_status' => 'refunded']);
        }

        $dispute->user->notify(new DisputeResolvedNotification($dispute));
        
        if ($dispute->seller && $dispute->seller->user) {
            $dispute->seller->user->notify(new DisputeResolvedNotification($dispute));
        }

        return back()->with('success', 'Dispute resolved successfully.');
    }

    public function addMessage(Request $request, $id)
    {
        $dispute = OrderDispute::findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:1000',
            'is_internal' => 'boolean',
        ]);

        $message = OrderDisputeMessage::create([
            'order_dispute_id' => $dispute->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        if (!$request->boolean('is_internal')) {
            $dispute->user->notify(new DisputeMessageNotification($message));
            
            if ($dispute->seller && $dispute->seller->user) {
                $dispute->seller->user->notify(new DisputeMessageNotification($message));
            }
        }

        return back()->with('success', 'Message sent successfully.');
    }
}
