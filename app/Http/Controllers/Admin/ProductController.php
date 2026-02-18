<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category']);
        
        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter by seller
        if ($request->seller) {
            $query->where('seller_id', $request->seller);
        }
        
        // Filter by category
        if ($request->category) {
            $query->where('category_id', $request->category);
        }
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        // Date filter
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.products.index', compact('products'));
    }

    /**
     * Pending products organizer: by category → shops → products requesting approval.
     */
    public function pendingOrganizer()
    {
        $categories = Category::orderBy('name')->get();
        $organized = [];

        foreach ($categories as $category) {
            $pendingProducts = Product::with(['seller', 'primaryImage'])
                ->where('category_id', $category->id)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($pendingProducts->isEmpty()) {
                continue;
            }

            $byShop = $pendingProducts->groupBy('seller_id');
            $shops = [];
            foreach ($byShop as $sellerId => $products) {
                $seller = $products->first()->seller;
                $shops[] = (object) [
                    'seller' => $seller,
                    'products' => $products,
                ];
            }

            $organized[] = (object) [
                'category' => $category,
                'shops' => $shops,
            ];
        }

        return view('admin.products.pending', compact('organized'));
    }

    /**
     * Approved products organizer: by category → shops → approved products list.
     */
    public function approvedOrganizer()
    {
        $categories = Category::orderBy('name')->get();
        $organized = [];

        foreach ($categories as $category) {
            $approvedProducts = Product::with(['seller', 'primaryImage'])
                ->where('category_id', $category->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            if ($approvedProducts->isEmpty()) {
                continue;
            }

            $byShop = $approvedProducts->groupBy('seller_id');
            $shops = [];
            foreach ($byShop as $sellerId => $products) {
                $seller = $products->first()->seller;
                $shops[] = (object) [
                    'seller' => $seller,
                    'products' => $products,
                ];
            }

            $organized[] = (object) [
                'category' => $category,
                'shops' => $shops,
            ];
        }

        return view('admin.products.approved', compact('organized'));
    }

    /**
     * Rejected products organizer: by category → shops → rejected products list.
     */
    public function rejectedOrganizer()
    {
        $categories = Category::orderBy('name')->get();
        $organized = [];

        foreach ($categories as $category) {
            $rejectedProducts = Product::with(['seller', 'primaryImage'])
                ->where('category_id', $category->id)
                ->where('status', 'inactive')
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($rejectedProducts->isEmpty()) {
                continue;
            }

            $byShop = $rejectedProducts->groupBy('seller_id');
            $shops = [];
            foreach ($byShop as $sellerId => $products) {
                $seller = $products->first()->seller;
                $shops[] = (object) [
                    'seller' => $seller,
                    'products' => $products,
                ];
            }

            $organized[] = (object) [
                'category' => $category,
                'shops' => $shops,
            ];
        }

        return view('admin.products.rejected', compact('organized'));
    }

    public function show($id)
    {
        $product = Product::with([
            'seller', 
            'category', 
            'images',
            'reviews' => function($q) {
                $q->with('user')->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);
        
        // Get reports for this product
        $reports = \App\Models\Report::where('reportable_type', Product::class)
            ->where('reportable_id', $product->id)
            ->with('reporter')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.products.show', compact('product', 'reports'));
    }
    
    public function edit($id)
    {
        $product = Product::with(['seller', 'category', 'images'])->findOrFail($id);
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,pending,sold_out',
            'amazon_reference_price' => 'nullable|numeric|min:0',
            'amazon_reference_image' => 'nullable|string|max:2000',
            'amazon_reference_url' => 'nullable|url|max:2000',
        ]);
        
        $product->update($request->only([
            'name', 'description', 'category_id', 'price',
            'stock_quantity', 'status', 'brand', 'sku',
            'amazon_reference_price', 'amazon_reference_image', 'amazon_reference_url',
        ]));
        
        return redirect()->route('admin.products.show', $product->id)
            ->with('success', 'Product updated successfully!');
    }
    
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function approve($id)
    {
        $product = Product::findOrFail($id);

        if ($product->status === 'active') {
            return back()->with('error', 'This product is already approved and cannot be approved again.');
        }

        $wasPending = $product->status === 'pending';
        $data = ['status' => 'active'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'condition')) {
            $data['condition'] = 'new';
        }
        $product->update($data);

        return back()->with('success', $wasPending ? 'Product approved!' : 'Product reactivated.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ]);

        $product = Product::with('seller.user')->findOrFail($id);

        if ($product->status !== 'pending') {
            return back()->with('error', 'Only pending products can be rejected. This product has already been reviewed.');
        }
        
        // Build rejection reason text
        $rejectionReasons = [
            'inappropriate_content' => 'Inappropriate Content',
            'misleading_information' => 'Misleading Information',
            'poor_quality_images' => 'Poor Quality Images',
            'incorrect_category' => 'Incorrect Category',
            'violates_policies' => 'Violates Platform Policies',
            'duplicate_product' => 'Duplicate Product',
            'incomplete_information' => 'Incomplete Information',
            'pricing_issues' => 'Pricing Issues',
            'other' => 'Other',
        ];
        
        $reasonText = $rejectionReasons[$request->rejection_reason] ?? $request->rejection_reason;
        $fullReason = $reasonText;
        
        if ($request->comment) {
            $fullReason .= "\n\nAdditional Comments:\n" . $request->comment;
        }
        
        $product->update([
            'status' => 'inactive',
            'rejection_reason' => $fullReason
        ]);
        
        // Send notification to seller
        if ($product->seller && $product->seller->user) {
            $product->seller->user->notify(
                new \App\Notifications\ProductRejectedNotification(
                    $product->name,
                    $fullReason,
                    $product->sku
                )
            );
        }
        
        return back()->with('success', 'Product rejected and seller has been notified.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'product_ids' => 'required|array',
        ]);

        $ids = array_unique($request->product_ids);
        $pending = Product::whereIn('id', $ids)->where('status', 'pending')->pluck('id')->all();

        if ($request->action === 'approve') {
            $data = ['status' => 'active'];
            if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'condition')) {
                $data['condition'] = 'new';
            }
            Product::whereIn('id', $pending)->update($data);
            $count = count($pending);
            $skipped = count($ids) - $count;
            $msg = 'Bulk action completed! ' . $count . ' product(s) approved.';
            if ($skipped > 0) {
                $msg .= ' ' . $skipped . ' product(s) skipped (already reviewed).';
            }
            return back()->with('success', $msg);
        }

        Product::whereIn('id', $pending)->update(['status' => 'inactive']);
        $count = count($pending);
        $skipped = count($ids) - $count;
        $msg = 'Bulk action completed! ' . $count . ' product(s) rejected.';
        if ($skipped > 0) {
            $msg .= ' ' . $skipped . ' product(s) skipped (only pending can be rejected).';
        }
        return back()->with('success', $msg);
    }
}
