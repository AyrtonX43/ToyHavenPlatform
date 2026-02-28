<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['seller', 'items.product', 'receipt', 'dispute'])
            ->where('user_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('toyshop.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['seller', 'items.product', 'tracking', 'receipt', 'dispute'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('toyshop.orders.show', compact('order'));
    }

    public function tracking($id)
    {
        $order = Order::with(['tracking', 'items.product'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('toyshop.orders.tracking', compact('order'));
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500',
        ]);

        DB::beginTransaction();
        try {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_by' => Auth::id(),
            ]);

            $order->restoreStock();

            if ($order->isPaid()) {
                $order->update(['payment_status' => 'refunded']);
            }

            OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'description' => 'Order cancelled by customer: ' . $request->cancellation_reason,
                'updated_by' => Auth::id(),
            ]);

            if ($order->seller && $order->seller->user) {
                $order->seller->user->notify(new OrderCancelledNotification($order));
            }

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Order cancelled successfully. Stock has been restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }

    public function retryPayment($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->payment_status !== 'pending') {
            return back()->with('error', 'This order payment cannot be retried.');
        }

        return redirect()->route('checkout.payment', $order->order_number);
    }
}
