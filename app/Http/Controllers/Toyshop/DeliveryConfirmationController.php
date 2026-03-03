<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\DeliveryConfirmation;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliveryConfirmationController extends Controller
{
    public function create(Order $order)
    {
        $order->load(['items.product', 'seller']);
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->isDelivered()) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Order must be delivered before confirmation.');
        }

        if ($order->isDeliveryConfirmed()) {
            return redirect()->route('orders.show', $order->id)
                ->with('info', 'Delivery already confirmed.');
        }

        if ($order->hasActiveDispute()) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Cannot confirm delivery while dispute is active.');
        }

        return view('toyshop.orders.confirm-delivery', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->isDelivered()) {
            return back()->with('error', 'Order must be delivered before confirmation.');
        }

        if ($order->isDeliveryConfirmed()) {
            return back()->with('info', 'Delivery already confirmed.');
        }

        if ($order->hasActiveDispute()) {
            return back()->with('error', 'Cannot confirm delivery while dispute is active.');
        }

        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $imagePath = $request->file('proof_image')->store(
            "delivery_proofs/{$order->user_id}",
            'public'
        );

        DeliveryConfirmation::create([
            'order_id' => $order->id,
            'proof_image_path' => $imagePath,
            'notes' => $request->notes,
            'confirmed_at' => now(),
            'auto_confirmed' => false,
        ]);

        \App\Jobs\SendReviewRequestJob::dispatch($order)->delay(now()->addDay());

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Delivery confirmed successfully! You can now review this product.');
    }

    public function show(Order $order)
    {
        $order->load(['deliveryConfirmation', 'items.product']);
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->isDeliveryConfirmed()) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'Delivery has not been confirmed yet.');
        }

        return view('toyshop.orders.delivery-confirmation', compact('order'));
    }
}
