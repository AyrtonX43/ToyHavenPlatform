<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'seller', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('moderator.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'seller', 'items.product', 'tracking', 'receipt', 'dispute'])
            ->findOrFail($id);

        return view('moderator.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,packed,shipped,in_transit,out_for_delivery,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'courier_name' => 'nullable|string|max:100',
            'estimated_delivery_date' => 'nullable|date',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        if ($request->filled('tracking_number')) {
            $updateData['tracking_number'] = $request->tracking_number;
        }

        if ($request->filled('courier_name')) {
            $updateData['courier_name'] = $request->courier_name;
        }

        if ($request->filled('estimated_delivery_date')) {
            $updateData['estimated_delivery_date'] = $request->estimated_delivery_date;
        }

        if ($newStatus === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        $order->user->notify(new OrderStatusUpdatedNotification($order, $oldStatus, $newStatus));

        if ($newStatus === 'delivered') {
            $order->user->notify(new OrderDeliveredNotification($order));
        }

        return back()->with('success', 'Order status updated successfully.');
    }
}
