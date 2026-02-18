<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
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
            'description' => 'nullable|string|max:500',
            'estimated_delivery_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
        ]);

        $statusMap = [
            'processing' => 'processing',
            'packed' => 'packed',
            'shipped' => 'shipped',
            'in_transit' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
        ];

        $trackingStatusMap = [
            'processing' => 'processing',
            'packed' => 'packed',
            'shipped' => 'shipped',
            'in_transit' => 'in_transit',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
        ];

        $order->update([
            'status' => $request->status,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number,
        ]);

        // Create tracking entry
        OrderTracking::create([
            'order_id' => $order->id,
            'status' => $trackingStatusMap[$request->status],
            'description' => $request->description ?? $this->getDefaultDescription($request->status),
            'tracking_number' => $request->tracking_number,
            'updated_by' => Auth::id(),
            'location' => $request->location,
            'estimated_delivery_date' => $request->estimated_delivery_date,
            'notes' => $request->notes,
        ]);

        // If delivered, update delivered_at
        if ($request->status === 'delivered') {
            $order->update(['delivered_at' => now()]);
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
            $order->update(['status' => $request->status]);

            OrderTracking::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'description' => $request->description ?? $this->getDefaultDescription($request->status),
                'updated_by' => Auth::id(),
            ]);

            if ($request->status === 'delivered') {
                $order->update(['delivered_at' => now()]);
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
