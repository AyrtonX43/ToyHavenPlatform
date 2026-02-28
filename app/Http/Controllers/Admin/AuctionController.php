<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Product;
use App\Models\UserProduct;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['user', 'category', 'winner']);

        if ($request->filled('status')) {
            if ($request->status === 'live') {
                $query->live();
            } elseif ($request->status === 'ended') {
                $query->ended();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($sql) => $sql->where('title', 'like', "%{$q}%"));
        }

        $auctions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.index', compact('auctions'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.auctions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starting_bid' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'end_at' => 'required|date|after:now',
            'start_at' => 'nullable|date',
            'status' => 'required|in:draft,live,pending_approval',
            'is_members_only' => 'boolean',
            'early_access_hours' => 'nullable|integer|min:0',
            'product_id' => 'nullable|exists:products,id',
            'user_product_id' => 'nullable|exists:user_products,id',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $validated['seller_id'] = null;
        if ($validated['product_id'] ?? null) {
            $product = Product::find($validated['product_id']);
            $validated['seller_id'] = $product?->seller_id;
        }
        $validated['is_members_only'] = $request->boolean('is_members_only');

        Auction::create($validated);

        return redirect()->route('admin.auctions.index')->with('success', 'Auction created.');
    }

    public function show(Auction $auction)
    {
        $auction->load(['user', 'seller', 'product', 'userProduct', 'category', 'categories', 'images', 'bids.user', 'winner']);

        return view('admin.auctions.show', compact('auction'));
    }

    public function edit(Auction $auction)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.auctions.edit', compact('auction', 'categories'));
    }

    public function update(Request $request, Auction $auction)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starting_bid' => 'required|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'end_at' => 'required|date',
            'status' => 'required|in:draft,live,ended,cancelled',
            'is_members_only' => 'boolean',
        ]);

        $validated['is_members_only'] = $request->boolean('is_members_only');

        $auction->update($validated);

        return redirect()->route('admin.auctions.show', $auction)->with('success', 'Auction updated.');
    }

    public function approve(Auction $auction)
    {
        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Auction is not pending approval.');
        }

        $auction->update([
            'status' => 'live',
            'start_at' => $auction->start_at ?? now(),
        ]);

        return back()->with('success', 'Auction approved and is now live.');
    }

    public function reject(Request $request, Auction $auction)
    {
        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Auction is not pending approval.');
        }

        $auction->update(['status' => 'cancelled']);

        return back()->with('success', 'Auction rejected.');
    }

    public function cancel(Auction $auction)
    {
        if ($auction->status === 'ended') {
            return back()->with('error', 'Cannot cancel an ended auction.');
        }

        $auction->update(['status' => 'cancelled']);

        return back()->with('success', 'Auction cancelled.');
    }

    public function destroy(Auction $auction)
    {
        $auction->delete();
        return redirect()->route('admin.auctions.index')->with('success', 'Auction deleted.');
    }
}
