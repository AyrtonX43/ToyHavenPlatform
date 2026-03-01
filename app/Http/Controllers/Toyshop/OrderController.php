<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['seller', 'items.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('toyshop.orders.index', compact('orders'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::with(['seller', 'items.product', 'tracking'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('toyshop.orders.show', compact('order'));
    }

    /**
     * Show order tracking page
     */
    public function tracking($id)
    {
        $order = Order::with(['tracking', 'items.product'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('toyshop.orders.tracking', compact('order'));
    }
}
