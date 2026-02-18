<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Seller;
use App\Models\SellerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function storeProductReview(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'review_images' => 'nullable|array|max:5',
            'review_images.*' => 'image|mimes:jpeg,jpg,png|max:2048',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $product = Product::findOrFail($productId);

        // Check if user already reviewed this product
        $existingReview = ProductReview::where('product_id', $productId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        // Verify order if provided
        if ($request->order_id) {
            $order = Order::where('id', $request->order_id)
                ->where('user_id', Auth::id())
                ->where('status', 'delivered')
                ->first();

            if (!$order) {
                return back()->with('error', 'Invalid order or order not delivered yet.');
            }
        }

        // Upload review images
        $imagePaths = [];
        if ($request->hasFile('review_images')) {
            foreach ($request->file('review_images') as $image) {
                $path = $image->store('reviews/' . Auth::id(), 'public');
                $imagePaths[] = $path;
            }
        }

        // Create review
        $review = ProductReview::create([
            'product_id' => $productId,
            'user_id' => Auth::id(),
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'review_images' => $imagePaths,
            'status' => 'pending', // Admin approval
        ]);

        // Update product rating
        $this->updateProductRating($product);

        return back()->with('success', 'Review submitted successfully! It will be visible after admin approval.');
    }

    public function storeSellerReview(Request $request, $sellerId)
    {
        $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'product_quality_rating' => 'nullable|integer|min:1|max:5',
            'shipping_rating' => 'nullable|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'order_id' => 'required|exists:orders,id',
        ]);

        $seller = Seller::findOrFail($sellerId);

        // Verify order
        $order = Order::where('id', $request->order_id)
            ->where('user_id', Auth::id())
            ->where('seller_id', $sellerId)
            ->where('status', 'delivered')
            ->first();

        if (!$order) {
            return back()->with('error', 'Invalid order or order not delivered yet.');
        }

        // Check if already reviewed
        $existingReview = SellerReview::where('seller_id', $sellerId)
            ->where('user_id', Auth::id())
            ->where('order_id', $request->order_id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this seller for this order.');
        }

        // Create review
        SellerReview::create([
            'seller_id' => $sellerId,
            'user_id' => Auth::id(),
            'order_id' => $request->order_id,
            'overall_rating' => $request->overall_rating,
            'product_quality_rating' => $request->product_quality_rating,
            'shipping_rating' => $request->shipping_rating,
            'communication_rating' => $request->communication_rating,
            'review_text' => $request->review_text,
        ]);

        // Update seller rating
        $this->updateSellerRating($seller);

        return back()->with('success', 'Review submitted successfully!');
    }

    private function updateProductRating(Product $product)
    {
        $reviews = ProductReview::where('product_id', $product->id)
            ->where('status', 'approved')
            ->get();

        if ($reviews->count() > 0) {
            $averageRating = $reviews->avg('rating');
            $product->update([
                'rating' => round($averageRating, 2),
                'reviews_count' => $reviews->count(),
            ]);
        }
    }

    private function updateSellerRating(Seller $seller)
    {
        $reviews = SellerReview::where('seller_id', $seller->id)->get();

        if ($reviews->count() > 0) {
            $averageRating = $reviews->avg('overall_rating');
            $seller->update([
                'rating' => round($averageRating, 2),
                'total_reviews' => $reviews->count(),
            ]);
        }
    }
}
