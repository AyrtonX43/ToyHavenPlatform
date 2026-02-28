<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\OrderDisputeMessage;
use App\Models\User;
use App\Notifications\DisputeOpenedNotification;
use App\Notifications\DisputeMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderDisputeController extends Controller
{
    public function create($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->canOpenDispute()) {
            return back()->with('error', 'Cannot open dispute for this order.');
        }

        return view('toyshop.disputes.create', compact('order'));
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->canOpenDispute()) {
            return back()->with('error', 'Cannot open dispute for this order.');
        }

        $request->validate([
            'reason' => 'required|in:not_received,damaged,wrong_item,incomplete,other',
            'description' => 'required|string|min:20|max:1000',
            'evidence_photos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $evidencePhotos = [];
        if ($request->hasFile('evidence_photos')) {
            foreach ($request->file('evidence_photos') as $photo) {
                $evidencePhotos[] = $photo->store('dispute-evidence', 'public');
            }
        }

        $dispute = OrderDispute::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'seller_id' => $order->seller_id,
            'dispute_number' => OrderDispute::generateDisputeNumber(),
            'reason' => $request->reason,
            'description' => $request->description,
            'evidence_photos' => $evidencePhotos,
            'status' => 'open',
        ]);

        $order->update(['has_dispute' => true]);

        OrderDisputeMessage::create([
            'order_dispute_id' => $dispute->id,
            'user_id' => Auth::id(),
            'message' => $request->description,
            'is_internal' => false,
        ]);

        if ($order->seller && $order->seller->user) {
            $order->seller->user->notify(new DisputeOpenedNotification($dispute, 'seller'));
        }

        $moderators = User::where('role', 'moderator')->orWhere('role', 'admin')->get();
        foreach ($moderators as $moderator) {
            $moderator->notify(new DisputeOpenedNotification($dispute, 'moderator'));
        }

        return redirect()->route('orders.disputes.show', $dispute->id)
            ->with('success', 'Dispute opened successfully. A moderator will review your case.');
    }

    public function show($disputeId)
    {
        $dispute = OrderDispute::with(['order', 'messages.user', 'seller', 'moderator'])
            ->where('user_id', Auth::id())
            ->findOrFail($disputeId);

        return view('toyshop.disputes.show', compact('dispute'));
    }

    public function addMessage(Request $request, $disputeId)
    {
        $dispute = OrderDispute::where('user_id', Auth::id())
            ->findOrFail($disputeId);

        if (!$dispute->isOpen()) {
            return back()->with('error', 'Cannot add messages to a closed dispute.');
        }

        $request->validate([
            'message' => 'required|string|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->store('dispute-attachments', 'public');
            }
        }

        $message = OrderDisputeMessage::create([
            'order_dispute_id' => $dispute->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachments' => $attachments,
            'is_internal' => false,
        ]);

        if ($dispute->seller && $dispute->seller->user) {
            $dispute->seller->user->notify(new DisputeMessageNotification($message));
        }

        if ($dispute->moderator) {
            $dispute->moderator->notify(new DisputeMessageNotification($message));
        }

        return back()->with('success', 'Message sent successfully.');
    }
}
