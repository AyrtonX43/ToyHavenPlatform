<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\UserProduct;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $products = UserProduct::where('user_id', Auth::id())
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('trading.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('trading.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'condition' => 'required|in:new,used,refurbished',
            'estimated_value' => 'nullable|numeric|min:0',
            'images' => 'required|array|min:4|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $product = UserProduct::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'brand' => $validated['brand'] ?? null,
            'condition' => $validated['condition'],
            'estimated_value' => $validated['estimated_value'] ?? null,
            'status' => 'available',
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $displayOrder = 0;
            foreach ($request->file('images') as $image) {
                $path = $image->store('user-products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => $displayOrder === 0,
                    'display_order' => $displayOrder++,
                ]);
            }
        }

        return redirect()->route('trading.products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = UserProduct::where('user_id', Auth::id())
            ->with(['category', 'images', 'user'])
            ->findOrFail($id);

        return view('trading.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = UserProduct::where('user_id', Auth::id())
            ->with(['images'])
            ->findOrFail($id);

        $categories = Category::orderBy('name')->get();

        return view('trading.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = UserProduct::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'nullable|string|max:255',
            'condition' => 'required|in:new,used,refurbished',
            'estimated_value' => 'nullable|numeric|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:user_product_images,id',
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'brand' => $validated['brand'] ?? null,
            'condition' => $validated['condition'],
            'estimated_value' => $validated['estimated_value'] ?? null,
        ]);

        // Remove selected images
        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            $maxOrder = $product->images()->max('display_order') ?? -1;
            foreach ($request->file('images') as $image) {
                $path = $image->store('user-products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => $product->images()->count() === 0,
                    'display_order' => ++$maxOrder,
                ]);
            }
        }

        // Update primary image if needed
        if ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
            $product->images()->first()->update(['is_primary' => true]);
        }

        $totalImages = $product->images()->count();
        if ($totalImages < 4 || $totalImages > 10) {
            return back()->withInput()->with('error', 'Product must have between 4 and 10 images. Currently has ' . $totalImages . '.');
        }

        return redirect()->route('trading.products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = UserProduct::where('user_id', Auth::id())
            ->with(['images'])
            ->findOrFail($id);

        // Check if product is in a trade
        if ($product->status === 'in_trade') {
            return redirect()->route('trading.products.index')
                ->with('error', 'Cannot delete product that is currently in a trade.');
        }

        // Delete images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('trading.products.index')
            ->with('success', 'Product deleted successfully!');
    }
}
