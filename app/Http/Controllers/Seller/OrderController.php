<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:seller,admin');
    }

    public function index()
    {
        $seller = Auth::user()->seller;
        
        $orders = Order::with(['user', 'items.product'])
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('seller.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $seller = Auth::user()->seller;
        $order = Order::with(['user', 'items.product', 'tracking'])
            ->where('seller_id', $seller->id)
            ->findOrFail($id);

        return view('seller.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $seller = Auth::user()->seller;
        $order = Order::where('seller_id', $seller->id)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:processing,packed,shipped,in_transit,out_for_delivery,delivered',
            'tracking_number' => 'nullable|string|max:100',
            'courier_name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'estimated_delivery_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        $updateData = [
            'status' => $newStatus,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number,
        ];

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

        OrderTracking::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'description' => $request->description ?? $this->getDefaultDescription($newStatus),
            'tracking_number' => $request->tracking_number,
            'updated_by' => Auth::id(),
            'location' => $request->location,
            'estimated_delivery_date' => $request->estimated_delivery_date,
            'notes' => $request->notes,
        ]);

        $order->user->notify(new OrderStatusUpdatedNotification($order, $oldStatus, $newStatus));

        if ($newStatus === 'delivered') {
            $order->user->notify(new OrderDeliveredNotification($order));
        }

        return back()->with('success', 'Order status updated successfully!');
    }

    public function bulkUpdate(Request $request)
    {
        $seller = Auth::user()->seller;

        $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|exists:orders,id',
            'status' => 'required|in:processing,packed,shipped,in_transit,out_for_delivery,delivered',
            'description' => 'nullable|string|max:500',
        ]);

        $orders = Order::where('seller_id', $seller->id)
            ->whereIn('id', $request->order_ids)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->get();

        $updatedCount = 0;
        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $updateData = ['status' => $request->status];
            
            if ($request->status === 'delivered') {
                $updateData['delivered_at'] = now();
            }
            
            $order->update($updateData);

            OrderTracking::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'description' => $request->description ?? $this->getDefaultDescription($request->status),
                'updated_by' => Auth::id(),
            ]);

            $order->user->notify(new OrderStatusUpdatedNotification($order, $oldStatus, $request->status));

            if ($request->status === 'delivered') {
                $order->user->notify(new OrderDeliveredNotification($order));
            }

            $updatedCount++;
        }

        return redirect()->route('seller.orders.index')
            ->with('success', "Successfully updated {$updatedCount} order(s)!");
    }

    private function getDefaultDescription($status)
    {
        return match($status) {
            'processing' => 'Seller is preparing your order',
            'packed' => 'Your order has been packed',
            'shipped' => 'Your order has been shipped',
            'in_transit' => 'Your order is in transit',
            'out_for_delivery' => 'Your order is out for delivery',
            'delivered' => 'Your order has been delivered',
            default => 'Order status updated',
        };
    }
}
