<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Services\PayMongoService;
use App\Services\PriceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $payMongoService;
    protected $priceService;

    public function __construct()
    {
        $this->payMongoService = app(PayMongoService::class);
        $this->priceService = app(PriceCalculationService::class);
    }

    public function index()
    {
        $cartItems = CartItem::with(['product.images', 'product.seller'])
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Validate all items are from active sellers
        $invalidItems = [];
        foreach ($cartItems as $item) {
            if (!$item->product || 
                $item->product->status !== 'active' || 
                !$item->product->seller || 
                !$item->product->seller->is_active || 
                $item->product->seller->verification_status !== 'approved') {
                $invalidItems[] = $item->product->name ?? 'Product';
                $item->delete();
            }
        }

        if (!empty($invalidItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Some items were removed because the seller is no longer active. Please review your cart.');
        }

        // Refresh cart items
        $cartItems = CartItem::with(['product.images', 'product.seller'])
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Group cart items by seller
        $itemsBySeller = $cartItems->groupBy('product.seller_id');

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        // Membership tier benefits: toyshop discount and free shipping
        $membershipDiscount = 0;
        $membershipDiscountPercent = 0;
        $freeShippingMin = null;
        $user = Auth::user();
        if ($user && $user->hasActiveMembership()) {
            $plan = $user->currentPlan();
            if ($plan) {
                $membershipDiscountPercent = $plan->getToyshopDiscount();
                if ($membershipDiscountPercent > 0) {
                    $membershipDiscount = $subtotal * ($membershipDiscountPercent / 100);
                }
                $freeShippingMin = $plan->getFreeShippingMin();
            }
        }

        $subtotalAfterDiscount = $subtotal - $membershipDiscount;

        $shippingFee = 0; // Can be calculated based on location
        if ($freeShippingMin !== null && $subtotalAfterDiscount >= $freeShippingMin) {
            $shippingFee = 0; // Free shipping for members
        }

        return view('toyshop.checkout.index', compact(
            'cartItems',
            'itemsBySeller',
            'subtotal',
            'subtotalAfterDiscount',
            'membershipDiscount',
            'membershipDiscountPercent',
            'freeShippingMin',
            'shippingFee'
        ));
    }

    public function process(CheckoutRequest $request)
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Validate all items are from active sellers and have stock
        foreach ($cartItems as $item) {
            // Check if product and seller are active
            if (!$item->product || 
                $item->product->status !== 'active' || 
                !$item->product->seller || 
                !$item->product->seller->is_active || 
                $item->product->seller->verification_status !== 'approved') {
                $item->delete();
                return redirect()->route('cart.index')
                    ->with('error', "Some items were removed because the seller is no longer active. Please review your cart.");
            }

            // Check stock availability
            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Insufficient stock for {$item->product->name}.");
            }
        }

            DB::beginTransaction();
        try {
            // Group items by seller (one order per seller)
            $itemsBySeller = $cartItems->groupBy('product.seller_id');
            $totalSubtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

            // Membership toyshop discount
            $membershipDiscountPct = 0;
            $user = Auth::user();
            if ($user && $user->hasActiveMembership()) {
                $plan = $user->currentPlan();
                if ($plan) {
                    $membershipDiscountPct = $plan->getToyshopDiscount();
                }
            }
            $totalDiscount = $totalSubtotal * ($membershipDiscountPct / 100);
            $createdOrders = [];

            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                // Calculate base amount (sum of product prices)
                $sellerSubtotal = $sellerItems->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                });
                // Apply membership discount proportionally
                $sellerDiscount = $totalSubtotal > 0 ? $totalDiscount * ($sellerSubtotal / $totalSubtotal) : 0;
                $baseAmount = $sellerSubtotal - $sellerDiscount;

                // Calculate commission, tax, and final price
                $priceCalculation = $this->priceService->calculatePrice($baseAmount);

                // Create order with commission and tax breakdown
                $order = Order::create([
                    'order_number' => 'TH' . time() . rand(1000, 9999),
                    'user_id' => Auth::id(),
                    'seller_id' => $sellerId,
                    'total_amount' => $baseAmount,
                    'admin_commission' => $priceCalculation['admin_commission'],
                    'admin_commission_rate' => $priceCalculation['admin_commission_rate'],
                    'tax_amount' => $priceCalculation['tax_amount'],
                    'tax_rate' => $priceCalculation['tax_rate'],
                    'transaction_fee' => $priceCalculation['transaction_fee'],
                    'seller_earnings' => $priceCalculation['seller_earnings'],
                    'shipping_fee' => 0, // Calculate based on location
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'shipping_address' => $request->shipping_address,
                    'shipping_phone' => $request->shipping_phone,
                    'shipping_city' => $request->shipping_city,
                    'shipping_province' => $request->shipping_province,
                    'shipping_postal_code' => $request->shipping_postal_code,
                    'shipping_notes' => $request->shipping_notes,
                ]);

                // Create order items
                foreach ($sellerItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                        'subtotal' => $cartItem->product->price * $cartItem->quantity,
                    ]);

                    // Update product stock
                    $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                }

                // Create initial tracking
                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'order_placed',
                    'description' => 'Order has been placed successfully.',
                    'updated_by' => Auth::id(),
                ]);

                $createdOrders[] = $order;
            }

            // Clear cart
            CartItem::where('user_id', Auth::id())->delete();

            DB::commit();

            // If only one order, redirect to payment
            // If multiple orders, redirect to orders list
            if (count($createdOrders) === 1) {
                return redirect()->route('checkout.payment', ['order_number' => $createdOrders[0]->order_number])
                    ->with('success', 'Order created successfully. Please complete payment.');
            } else {
                return redirect()->route('orders.index')
                    ->with('success', count($createdOrders) . ' orders created successfully. Please complete payment for each order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process order. Please try again.');
        }
    }

    public function payment($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order->id)
                ->with('info', 'This order has already been paid.');
        }

        return view('toyshop.checkout.payment', compact('order'));
    }

    public function paymentCallback(Request $request)
    {
        // Handle PayMongo webhook/callback
        // This will be implemented with PayMongo integration
        return response()->json(['status' => 'success']);
    }
}
