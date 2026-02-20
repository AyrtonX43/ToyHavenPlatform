<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:seller,admin');
    }

    public function index()
    {
        $seller = Auth::user()->seller;

        if (!$seller || $seller->verification_status !== 'approved') {
            return redirect()->route('seller.dashboard')
                ->with('error', 'You need to be an approved seller to access POS.');
        }

        // Get active products for POS
        $products = Product::where('seller_id', $seller->id)
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->with('images')
            ->orderBy('name')
            ->get();

        // POS stats - use shipping_notes to identify POS orders (contain 'POS Order')
        $posOrdersQuery = Order::where('seller_id', $seller->id)->where('shipping_notes', 'like', 'POS Order%');
        $stats = [
            'today_pos_sales' => (float) (clone $posOrdersQuery)->whereDate('created_at', today())->sum('total_amount'),
            'today_pos_orders' => (clone $posOrdersQuery)->whereDate('created_at', today())->count(),
            'week_pos_sales' => (float) (clone $posOrdersQuery)->where('created_at', '>=', now()->startOfWeek())->sum('total_amount'),
            'week_pos_orders' => (clone $posOrdersQuery)->where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        // Chart data - Last 7 days POS sales
        $salesChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $salesChartData[] = [
                'date' => $date->format('M d'),
                'sales' => (float) Order::where('seller_id', $seller->id)
                    ->where('shipping_notes', 'like', 'POS Order%')
                    ->whereDate('created_at', $date)
                    ->sum('total_amount'),
                'orders' => Order::where('seller_id', $seller->id)
                    ->where('shipping_notes', 'like', 'POS Order%')
                    ->whereDate('created_at', $date)
                    ->count()
            ];
        }

        // Payment method breakdown (last 30 days POS orders)
        $paymentBreakdown = Order::where('seller_id', $seller->id)
            ->where('shipping_notes', 'like', 'POS Order%')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('payment_method, count(*) as count, sum(total_amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Recent POS transactions
        $recentPosOrders = Order::where('seller_id', $seller->id)
            ->where('shipping_notes', 'like', 'POS Order%')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('seller.pos.index', compact('products', 'seller', 'stats', 'salesChartData', 'paymentBreakdown', 'recentPosOrders'));
    }

    public function processOrder(Request $request)
    {
        $seller = Auth::user()->seller;

        if (!$seller || $seller->verification_status !== 'approved') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,card,digital',
        ]);

        $items = $request->items;
        $total = 0;
        $orderItems = [];

        // Validate stock and calculate total
        foreach ($items as $item) {
            $product = Product::where('seller_id', $seller->id)
                ->where('id', $item['product_id'])
                ->firstOrFail();

            if ($product->stock_quantity < $item['quantity']) {
                return response()->json([
                    'error' => "Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"
                ], 400);
            }

            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            $orderItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal,
            ];
        }

        // Create order
        $order = Order::create([
            'order_number' => 'POS-' . strtoupper(Str::random(8)),
            'user_id' => null, // POS orders may not have a registered user
            'seller_id' => $seller->id,
            'total_amount' => $total,
            'shipping_fee' => 0,
            'total' => $total,
            'status' => 'completed', // POS orders are immediately completed
            'payment_status' => 'paid',
            'payment_method' => $request->payment_method,
            'shipping_address' => $request->customer_address ?? 'Walk-in Customer',
            'shipping_city' => $seller->city ?? '',
            'shipping_province' => $seller->province ?? '',
            'shipping_postal_code' => $seller->postal_code ?? '',
            'shipping_phone' => $request->customer_phone ?? $seller->phone ?? '',
            'shipping_notes' => 'POS Order - Customer: ' . ($request->customer_name ?? 'Walk-in'),
        ]);

        // Create order items and update stock
        foreach ($orderItems as $itemData) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData['product']->id,
                'product_name' => $itemData['product']->name,
                'quantity' => $itemData['quantity'],
                'price' => $itemData['price'],
                'subtotal' => $itemData['subtotal'],
            ]);

            // Update stock
            $itemData['product']->decrement('stock_quantity', $itemData['quantity']);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
            'message' => 'Order processed successfully!'
        ]);
    }
}
