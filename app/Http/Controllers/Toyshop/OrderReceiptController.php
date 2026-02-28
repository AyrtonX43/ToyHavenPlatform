<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReceipt;
use App\Notifications\OrderReceiptConfirmedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderReceiptController extends Controller
{
    public function store(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->isDelivered()) {
            return back()->with('error', 'Order must be delivered before confirming receipt.');
        }

        if ($order->hasReceipt()) {
            return back()->with('error', 'Receipt already confirmed for this order.');
        }

        if ($order->has_dispute) {
            return back()->with('error', 'Cannot confirm receipt for an order with an active dispute.');
        }

        $request->validate([
            'proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'delivery_notes' => 'nullable|string|max:500',
        ]);

        $photoPath = $request->file('proof_photo')->store('order-receipts', 'public');

        $receipt = OrderReceipt::create([
            'order_id' => $order->id,
            'receipt_number' => OrderReceipt::generateReceiptNumber(),
            'proof_photo_path' => $photoPath,
            'delivery_notes' => $request->delivery_notes,
            'confirmed_at' => now(),
        ]);

        $order->update([
            'receipt_confirmed_at' => now(),
        ]);

        if ($order->seller && $order->seller->user) {
            $order->seller->user->notify(new OrderReceiptConfirmedNotification($order));
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Receipt confirmed successfully! You can now review the products.');
    }

    public function show($orderId)
    {
        $order = Order::with('receipt')
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->receipt) {
            return redirect()->route('orders.show', $order->id)
                ->with('error', 'No receipt found for this order.');
        }

        return view('toyshop.orders.receipt', compact('order'));
    }
}
