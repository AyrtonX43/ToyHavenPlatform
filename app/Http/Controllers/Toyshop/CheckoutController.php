<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Services\LbcShippingService;
use App\Services\PayMongoService;
use App\Services\PriceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $payMongoService;
    protected $priceService;
    protected $lbcShipping;

    public function __construct()
    {
        $this->payMongoService = app(PayMongoService::class);
        $this->priceService = app(PriceCalculationService::class);
        $this->lbcShipping = app(LbcShippingService::class);
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

        $user = Auth::user();
        $defaultAddress = $user->defaultAddress;
        $addressData = [
            'shipping_address' => old('shipping_address', $defaultAddress?->address ?? $user->address),
            'shipping_city' => old('shipping_city', $defaultAddress?->city ?? $user->city),
            'shipping_province' => old('shipping_province', $defaultAddress?->province ?? $user->province),
            'shipping_postal_code' => old('shipping_postal_code', $defaultAddress?->postal_code ?? $user->postal_code),
            'shipping_phone' => old('shipping_phone', $user->phone ? (preg_match('/^\+?63/', $user->phone) ? $user->phone : '+63' . preg_replace('/\D/', '', $user->phone)) : ''),
            'shipping_notes' => old('shipping_notes', ''),
        ];

        $weightKg = $this->lbcShipping->calculateCartWeight($cartItems);
        $province = $addressData['shipping_province'] ?: 'Metro Manila';
        $city = $addressData['shipping_city'] ?: 'Manila';
        $estimatedShipping = $this->lbcShipping->estimate($province, $city, $weightKg);

        $shippingFee = 0;
        if ($freeShippingMin !== null && $subtotalAfterDiscount >= $freeShippingMin) {
            $shippingFee = 0;
        } else {
            $shippingFee = $estimatedShipping;
        }

        return view('toyshop.checkout.index', compact(
            'cartItems',
            'itemsBySeller',
            'subtotal',
            'subtotalAfterDiscount',
            'membershipDiscount',
            'membershipDiscountPercent',
            'freeShippingMin',
            'shippingFee',
            'addressData',
            'weightKg'
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

            // Membership toyshop discount and free shipping
            $membershipDiscountPct = 0;
            $freeShippingMin = null;
            $user = Auth::user();
            if ($user && $user->hasActiveMembership()) {
                $plan = $user->currentPlan();
                if ($plan) {
                    $membershipDiscountPct = $plan->getToyshopDiscount();
                    $freeShippingMin = $plan->getFreeShippingMin();
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
                $sellerCount = $itemsBySeller->count();
                $weightKg = $this->lbcShipping->calculateCartWeight($cartItems);
                $shippingFeeForOrder = 0;
                if ($freeShippingMin === null || $totalSubtotal - $totalDiscount < $freeShippingMin) {
                    $totalShipping = $this->lbcShipping->estimate(
                        $request->shipping_province,
                        $request->shipping_city,
                        $weightKg
                    );
                    $shippingFeeForOrder = round($totalShipping / $sellerCount, 2);
                }

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
                    'shipping_fee' => $shippingFeeForOrder,
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

        $publicKey = config('services.paymongo.public_key');

        return view('toyshop.checkout.payment', compact('order', 'publicKey'));
    }

    /**
     * Attach payment method to intent (server-side - uses secret key)
     */
    public function attachPaymentMethod(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'payment_intent_id' => 'required|string',
            'payment_method_id' => 'required|string',
            'return_url' => 'nullable|url',
        ]);

        $order = Order::where('order_number', $request->order_number)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        $result = $this->payMongoService->attachPaymentMethod(
            $request->payment_intent_id,
            $request->payment_method_id,
            $request->return_url
        );

        if (! $result) {
            return response()->json(['error' => 'Failed to attach payment method'], 500);
        }

        $status = $result['attributes']['status'] ?? null;
        $nextAction = $result['attributes']['next_action'] ?? null;

        return response()->json([
            'status' => $status,
            'next_action' => $nextAction,
            'redirect_url' => $nextAction['redirect']['url'] ?? null,
        ]);
    }

    /**
     * Create a PayMongo Payment Intent (AJAX) for the payment page
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate(['order_number' => 'required|string']);

        $order = Order::where('order_number', $request->order_number)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid'], 400);
        }

        $totalAmount = $order->total;

        $intent = $this->payMongoService->createPaymentIntent(
            $totalAmount,
            'PHP',
            ['order_number' => $order->order_number, 'order_id' => (string) $order->id]
        );

        if (! $intent) {
            return response()->json(['error' => 'Failed to create payment'], 500);
        }

        $attrs = $intent['attributes'] ?? $intent;

        return response()->json([
            'client_key' => $attrs['client_key'] ?? null,
            'id' => $intent['id'] ?? null,
        ]);
    }

    /**
     * Handle return from PayMongo (GET - user redirected after GCash/3DS)
     * Also handles POST from legacy callback (input works for both query and body)
     */
    public function paymentReturn(Request $request)
    {
        $orderNumber = $request->input('order_number');
        $paymentIntentId = $request->input('payment_intent_id');

        if (! $orderNumber || ! $paymentIntentId) {
            return redirect()->route('orders.index')->with('error', 'Invalid payment return.');
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->first();

        if (! $order) {
            return redirect()->route('orders.index')->with('error', 'Order not found.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order->id)->with('success', 'Payment already completed.');
        }

        $intent = $this->payMongoService->getPaymentIntent($paymentIntentId);
        $attrs = $intent['attributes'] ?? $intent ?? [];
        $status = $attrs['status'] ?? null;

        if ($status === 'succeeded') {
            $order->update([
                'payment_status' => 'paid',
                'payment_reference' => $paymentIntentId,
            ]);
            OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'payment_confirmed',
                'description' => 'Payment confirmed.',
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('orders.show', $order->id)->with('success', 'Payment successful!');
        }

        if ($status === 'awaiting_payment_method') {
            $lastError = $attrs['last_payment_error'] ?? null;

            return redirect()
                ->route('checkout.payment', $order->order_number)
                ->with('error', $lastError ? 'Payment could not be completed. Please try again.' : 'Payment was cancelled or failed.');
        }

        return redirect()
            ->route('checkout.payment', $order->order_number)
            ->with('error', 'Payment status could not be verified. Please try again.');
    }

    public function paymentCallback(Request $request)
    {
        // Kept for any POST callbacks; main return flow uses paymentReturn (GET)
        return $this->paymentReturn($request);
    }
}
