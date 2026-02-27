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
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected PayMongoService $payMongoService;
    protected PriceCalculationService $priceService;

    public function __construct()
    {
        $this->payMongoService = app(PayMongoService::class);
        $this->priceService = app(PriceCalculationService::class);
    }

    public function index(Request $request)
    {
        $query = CartItem::with(['product.images', 'product.seller'])
            ->where('user_id', Auth::id());

        $selectedIds = $request->input('cart_items', []);
        if (is_string($selectedIds)) {
            $selectedIds = array_filter(array_map('intval', explode(',', $selectedIds)));
        }
        $selectedIds = array_values((array) $selectedIds);

        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $cartItems = $query->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Please select at least one item to checkout.');
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

        // Shipping fee set to 0 - no logistics integrated yet
        $shippingFee = 0;

        // Get user's default address for auto-fill (from profile addresses)
        $defaultAddress = Auth::user()?->defaultAddress ?? Auth::user()?->addresses()->first();

        // VAT and price breakdown (using PriceCalculationService)
        $priceBreakdown = $this->priceService->calculatePrice($subtotalAfterDiscount);
        $vatAmount = $priceBreakdown['tax_amount'];
        $vatRate = $priceBreakdown['tax_rate'];
        $totalWithVat = $subtotalAfterDiscount + $shippingFee + $priceBreakdown['admin_commission'] + $priceBreakdown['tax_amount'] + $priceBreakdown['transaction_fee'];

        // Expected delivery: 3-5 business days from now
        $minDeliveryDate = now()->addWeekdays(3);
        $maxDeliveryDate = now()->addWeekdays(5);

        return view('toyshop.checkout.index', compact(
            'cartItems',
            'itemsBySeller',
            'subtotal',
            'subtotalAfterDiscount',
            'membershipDiscount',
            'membershipDiscountPercent',
            'freeShippingMin',
            'shippingFee',
            'defaultAddress',
            'priceBreakdown',
            'vatAmount',
            'vatRate',
            'totalWithVat',
            'minDeliveryDate',
            'maxDeliveryDate',
            'selectedIds'
        ));
    }

    public function process(CheckoutRequest $request)
    {
        $query = CartItem::with('product')
            ->where('user_id', Auth::id());

        $selectedIds = $request->input('cart_item_ids', []);
        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $cartItems = $query->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Please select at least one item to checkout.');
        }

        // Validate all items are from active sellers and have stock
        foreach ($cartItems as $item) {
            if (!$item->product || 
                $item->product->status !== 'active' || 
                !$item->product->seller || 
                !$item->product->seller->is_active || 
                $item->product->seller->verification_status !== 'approved') {
                $item->delete();
                return redirect()->route('cart.index')
                    ->with('error', "Some items were removed because the seller is no longer active. Please review your cart.");
            }

            if ($item->product->stock_quantity < $item->quantity) {
                return back()->with('error', "Insufficient stock for {$item->product->name}.");
            }
        }

        DB::beginTransaction();
        try {
            $itemsBySeller = $cartItems->groupBy('product.seller_id');
            $totalSubtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);

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
                $sellerSubtotal = $sellerItems->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                });
                $sellerDiscount = $totalSubtotal > 0 ? $totalDiscount * ($sellerSubtotal / $totalSubtotal) : 0;
                $baseAmount = $sellerSubtotal - $sellerDiscount;

                $priceCalculation = $this->priceService->calculatePrice($baseAmount);

                $estimatedDelivery = now()->addWeekdays(4);

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
                    'shipping_fee' => 0,
                    'estimated_delivery_date' => $estimatedDelivery,
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

                foreach ($sellerItems as $cartItem) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'quantity' => $cartItem->quantity,
                        'price' => $cartItem->product->price,
                        'subtotal' => $cartItem->product->price * $cartItem->quantity,
                    ]);

                    $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                }

                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'order_placed',
                    'description' => 'Order has been placed successfully.',
                    'updated_by' => Auth::id(),
                ]);

                $createdOrders[] = $order;
            }

            CartItem::whereIn('id', $cartItems->pluck('id'))->delete();

            DB::commit();

            if (count($createdOrders) === 1) {
                return redirect()->route('checkout.payment', ['order_number' => $createdOrders[0]->order_number])
                    ->with('success', 'Order created successfully. Please complete payment.');
            } else {
                return redirect()->route('orders.index')
                    ->with('success', count($createdOrders) . ' orders created successfully. Please complete payment for each order.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout process failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => Auth::id(),
            ]);
            return back()->with('error', 'Failed to process order: ' . $e->getMessage());
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

        $intent = $this->payMongoService->createPaymentIntent(
            $order->total,
            'PHP',
            ['order_number' => $order->order_number, 'order_id' => (string) $order->id]
        );

        $paymentIntentId = $intent['id'] ?? null;
        $clientKey = data_get($intent, 'attributes.client_key');

        return view('toyshop.checkout.payment', compact('order', 'publicKey', 'paymentIntentId', 'clientKey'));
    }

    /**
     * Create a PayMongo Payment Intent (AJAX).
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

        $intent = $this->payMongoService->createPaymentIntent(
            $order->total,
            'PHP',
            ['order_number' => $order->order_number, 'order_id' => (string) $order->id]
        );

        if (! $intent) {
            return response()->json(['error' => 'Failed to create payment. Please try again.'], 500);
        }

        $attrs = $intent['attributes'] ?? $intent;

        return response()->json([
            'client_key' => $attrs['client_key'] ?? null,
            'id' => $intent['id'] ?? null,
        ]);
    }

    /**
     * Server-side: attach payment method to intent via secret key.
     */
    public function processPayment(Request $request, $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order already paid.'], 400);
        }

        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $intent = $this->payMongoService->createPaymentIntent(
            $order->total,
            'PHP',
            ['order_number' => $order->order_number, 'order_id' => (string) $order->id]
        );

        if (! $intent) {
            return response()->json(['error' => 'Failed to create payment session.'], 500);
        }

        $paymentIntentId = $intent['id'];

        $returnUrl = url('/checkout/return') . '?' . http_build_query([
            'order_number' => $order->order_number,
            'payment_intent_id' => $paymentIntentId,
        ]);

        $result = $this->payMongoService->attachPaymentMethod(
            $paymentIntentId,
            $request->payment_method_id,
            $returnUrl
        );

        if (! $result) {
            return response()->json(['error' => 'Failed to process payment. Please try again.'], 500);
        }

        $status = $result['attributes']['status'] ?? 'unknown';
        $nextAction = $result['attributes']['next_action'] ?? null;

        Log::info('Checkout: processPayment result', [
            'order_number' => $order->order_number,
            'status' => $status,
            'next_action_json' => json_encode($nextAction),
        ]);

        if ($status === 'succeeded') {
            return response()->json([
                'status' => 'succeeded',
                'redirect_url' => $returnUrl,
            ]);
        }

        if ($status === 'awaiting_next_action') {
            $redirectUrl = $this->extractRedirectUrl($nextAction);
            return response()->json([
                'status' => 'awaiting_next_action',
                'redirect_url' => $redirectUrl,
                'next_action' => $nextAction,
            ]);
        }

        if ($status === 'processing') {
            return response()->json([
                'status' => 'processing',
                'redirect_url' => $returnUrl,
            ]);
        }

        if ($status === 'awaiting_payment_method') {
            $errorMsg = $result['attributes']['last_payment_error']['message']
                ?? 'Payment failed. Please try again with a different payment method.';
            return response()->json(['error' => $errorMsg], 400);
        }

        return response()->json(['error' => "Payment returned unexpected status: {$status}"], 400);
    }

    private function extractRedirectUrl($nextAction): ?string
    {
        if (is_string($nextAction)) {
            return filter_var($nextAction, FILTER_VALIDATE_URL) ? $nextAction : null;
        }
        if (! is_array($nextAction)) {
            return null;
        }
        if (! empty($nextAction['redirect']['url'])) {
            return $nextAction['redirect']['url'];
        }
        if (! empty($nextAction['url'])) {
            return $nextAction['url'];
        }
        foreach ($nextAction as $value) {
            if (is_array($value)) {
                $found = $this->extractRedirectUrl($value);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }

    /**
     * Handle return from PayMongo after 3DS/GCash/PayMaya redirect.
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
        $status = data_get($intent, 'attributes.status');

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

            $order->seller?->user?->notify(new \App\Notifications\OrderPaidNotification($order));

            return redirect()->route('orders.show', $order->id)->with('success', 'Payment successful!');
        }

        if ($status === 'awaiting_payment_method') {
            return redirect()
                ->route('checkout.payment', $order->order_number)
                ->with('error', 'Payment was cancelled or failed. Please try again.');
        }

        return redirect()
            ->route('checkout.payment', $order->order_number)
            ->with('error', 'Payment could not be verified. Please try again or check your order status.');
    }
}
