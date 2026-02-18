<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = CartItem::with(['product.images', 'product.seller', 'variation'])
            ->where('user_id', Auth::id())
            ->get();

        // Remove items from suspended/inactive sellers
        $removedItems = [];
        foreach ($cartItems as $item) {
            if (!$item->product || 
                $item->product->status !== 'active' || 
                !$item->product->seller || 
                !$item->product->seller->is_active || 
                $item->product->seller->verification_status !== 'approved') {
                $removedItems[] = $item->product->name ?? 'Product';
                $item->delete();
            }
        }

        // Refresh cart items after cleanup
        $cartItems = CartItem::with(['product.images', 'product.seller', 'variation'])
            ->where('user_id', Auth::id())
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $message = null;
        if (!empty($removedItems)) {
            $message = 'Some items were removed from your cart because the seller is no longer active.';
        }

        return view('toyshop.cart.index', compact('cartItems', 'subtotal', 'message'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'product_variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::with(['seller', 'variations'])->findOrFail($request->product_id);

        // Check if product is active
        if ($product->status !== 'active') {
            return back()->with('error', 'This product is not available.');
        }

        // Check if seller is active and approved
        if (!$product->seller || !$product->seller->is_active || $product->seller->verification_status !== 'approved') {
            return back()->with('error', 'This product is not available. The seller account is suspended.');
        }

        $variation = null;
        $productVariationId = $request->product_variation_id ? (int) $request->product_variation_id : null;

        if ($product->variations->isNotEmpty()) {
            // Product has options: variation must be selected and belong to this product
            if (!$productVariationId) {
                return back()->with('error', 'Please select a product option (e.g. color, size) before adding to cart.');
            }
            $variation = ProductVariation::where('product_id', $product->id)->where('id', $productVariationId)->first();
            if (!$variation || !$variation->is_available) {
                return back()->with('error', 'Selected option is not available.');
            }
            $stock = $variation->stock_quantity;
            if ($stock < $request->quantity) {
                return back()->with('error', 'Insufficient stock for the selected option.');
            }
        } else {
            // No variations: use product stock
            if ($product->stock_quantity < $request->quantity) {
                return back()->with('error', 'Insufficient stock available.');
            }
        }

        // Match cart line by product + variation (same variation = same line)
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->where(function ($q) use ($productVariationId) {
                if ($productVariationId) {
                    $q->where('product_variation_id', $productVariationId);
                } else {
                    $q->whereNull('product_variation_id');
                }
            })
            ->first();

        $maxQty = $variation ? $variation->stock_quantity : $product->stock_quantity;
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            if ($maxQty < $newQuantity) {
                return back()->with('error', 'Insufficient stock available.');
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'product_variation_id' => $productVariationId,
                'quantity' => $request->quantity,
            ]);
        }

        return back()->with('success', 'Product added to cart successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::with(['product.seller', 'variation'])->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        // Check if product and seller are still active
        if (!$cartItem->product || 
            $cartItem->product->status !== 'active' || 
            !$cartItem->product->seller || 
            !$cartItem->product->seller->is_active || 
            $cartItem->product->seller->verification_status !== 'approved') {
            $cartItem->delete();
            return back()->with('error', 'This product is no longer available. Item removed from cart.');
        }

        $maxQty = $cartItem->variation
            ? $cartItem->variation->stock_quantity
            : $cartItem->product->stock_quantity;
        if ($maxQty < $request->quantity) {
            return back()->with('error', 'Insufficient stock available.');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated successfully!');
    }

    public function remove($id)
    {
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $cartItem->delete();

        return back()->with('success', 'Item removed from cart.');
    }
}
