<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\User;
use App\Notifications\DisputeCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderDisputeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($orderId)
    {
        $order = Order::with(['items.product', 'seller'])
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->hasActiveDispute()) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'This order already has an active dispute.');
        }

        if (!$order->isDelivered()) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'You can only report issues for delivered orders.');
        }

        return view('toyshop.disputes.create', compact('order'));
    }

    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->hasActiveDispute()) {
            return back()->with('error', 'This order already has an active dispute.');
        }

        $request->validate([
            'type' => 'required|in:not_received,damaged,wrong_item,incomplete,other',
            'description' => 'required|string|min:20|max:1000',
            'evidence_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $evidenceImages = [];
        if ($request->hasFile('evidence_images')) {
            foreach ($request->file('evidence_images') as $image) {
                $path = $image->store("disputes/{$order->id}", 'public');
                $evidenceImages[] = $path;
            }
        }

        $dispute = OrderDispute::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'seller_id' => $order->seller_id,
            'type' => $request->type,
            'description' => $request->description,
            'evidence_images' => $evidenceImages,
            'status' => 'open',
        ]);

        $order->seller->user->notify(new DisputeCreatedNotification($dispute));
        
        $moderators = User::whereIn('role', ['moderator', 'admin'])->get();
        foreach ($moderators as $moderator) {
            $moderator->notify(new DisputeCreatedNotification($dispute));
        }

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Dispute created successfully. A moderator will review your case.');
    }

    public function index()
    {
        $disputes = OrderDispute::with(['order', 'seller'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('toyshop.disputes.index', compact('disputes'));
    }

    public function show($id)
    {
        $dispute = OrderDispute::with(['order.items.product', 'seller', 'assignedTo', 'resolvedBy'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('toyshop.disputes.show', compact('dispute'));
    }
}
