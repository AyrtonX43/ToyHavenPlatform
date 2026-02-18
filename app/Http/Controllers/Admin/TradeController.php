<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\TradeListing;
use App\Models\TradeOffer;
use App\Notifications\TradeListingApprovedNotification;
use App\Notifications\TradeListingRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Trade::with([
            'tradeListing.user',
            'tradeListing.seller',
            'initiator',
            'participant',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tradeListing', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $trades = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.trades.index', compact('trades'));
    }

    public function show($id)
    {
        $trade = Trade::with([
            'tradeListing.product.images',
            'tradeListing.userProduct.images',
            'tradeOffer.offeredProduct.images',
            'tradeOffer.offeredUserProduct.images',
            'initiator',
            'participant',
            'items',
        ])->findOrFail($id);

        return view('admin.trades.show', compact('trade'));
    }

    public function resolveDispute(Request $request, $id)
    {
        $trade = Trade::findOrFail($id);

        if ($trade->status !== 'disputed') {
            return back()->with('error', 'This trade is not in dispute.');
        }

        $validated = $request->validate([
            'resolution' => 'required|in:completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['resolution'] === 'completed') {
                $trade->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Update product statuses
                foreach ($trade->items as $item) {
                    if ($item->product_id) {
                        \App\Models\Product::where('id', $item->product_id)
                            ->update(['trade_status' => 'traded']);
                    } elseif ($item->user_product_id) {
                        \App\Models\UserProduct::where('id', $item->user_product_id)
                            ->update(['status' => 'traded']);
                    }
                }

                $trade->tradeListing->update(['status' => 'completed']);
            } else {
                // Cancel trade and return products to available
                foreach ($trade->items as $item) {
                    if ($item->product_id) {
                        \App\Models\Product::where('id', $item->product_id)
                            ->update(['trade_status' => 'available_for_trade']);
                    } elseif ($item->user_product_id) {
                        \App\Models\UserProduct::where('id', $item->user_product_id)
                            ->update(['status' => 'available']);
                    }
                }

                $trade->update(['status' => 'cancelled']);
                $trade->tradeListing->update(['status' => 'active']);
            }

            DB::commit();

            return back()->with('success', 'Dispute resolved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to resolve dispute: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $trade = Trade::findOrFail($id);

        if ($trade->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed trade.');
        }

        DB::beginTransaction();
        try {
            // Return products to available
            foreach ($trade->items as $item) {
                if ($item->product_id) {
                    \App\Models\Product::where('id', $item->product_id)
                        ->update(['trade_status' => 'available_for_trade']);
                } elseif ($item->user_product_id) {
                    \App\Models\UserProduct::where('id', $item->user_product_id)
                        ->update(['status' => 'available']);
                }
            }

            $trade->update(['status' => 'cancelled']);
            $trade->tradeListing->update(['status' => 'active']);

            DB::commit();

            return back()->with('success', 'Trade cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel trade: ' . $e->getMessage());
        }
    }

    public function listings(Request $request)
    {
        $query = TradeListing::with(['user', 'seller', 'product', 'userProduct', 'images', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $listings = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.trades.listings', compact('listings'));
    }

    public function showListing($id)
    {
        $listing = TradeListing::with([
            'user',
            'product.images',
            'product.category',
            'userProduct.images',
            'userProduct.category',
            'images',
            'category',
            'activeOffers.offerer',
            'activeOffers.offererSeller',
            'activeOffers.offeredProduct',
            'activeOffers.offeredUserProduct',
        ])->findOrFail($id);

        return view('admin.trades.listing-show', compact('listing'));
    }

    public function approveListing($id)
    {
        $listing = TradeListing::findOrFail($id);

        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'Only listings pending review can be approved.');
        }

        $listing->update(['status' => 'active']);
        $listing->user->notify(new TradeListingApprovedNotification($listing));
        return back()->with('success', 'Listing approved. It is now visible on the marketplace. The user has been notified.');
    }

    public function rejectListing(Request $request, $id)
    {
        $listing = TradeListing::with('user')->findOrFail($id);

        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'Only listings pending review can be rejected.');
        }

        $reason = $request->input('rejection_reason');

        $listing->update(['status' => 'rejected']);
        $listing->user->notify(new TradeListingRejectedNotification($listing, $reason));
        return back()->with('success', 'Listing rejected. The user has been notified.');
    }

    public function deleteListing($id)
    {
        $listing = TradeListing::with(['images', 'offers'])->findOrFail($id);

        if ($listing->status === 'pending_trade') {
            return back()->with('error', 'Cannot delete listing with active trade.');
        }

        DB::beginTransaction();
        try {
            // Return product to available
            if ($listing->product_id) {
                \App\Models\Product::where('id', $listing->product_id)
                    ->update(['trade_status' => 'available_for_trade']);
            } elseif ($listing->user_product_id) {
                \App\Models\UserProduct::where('id', $listing->user_product_id)
                    ->update(['status' => 'available']);
            }

            // Delete related records then the listing
            $listing->images()->delete();
            $listing->offers()->delete();
            $listing->delete();
            DB::commit();

            return back()->with('success', 'Listing deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete listing: ' . $e->getMessage());
        }
    }

    /**
     * Approved trade listings organized by category.
     */
    public function approvedListings()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        $organized = [];

        foreach ($categories as $category) {
            $listings = TradeListing::with(['user', 'images', 'category'])
                ->where('category_id', $category->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($listings->isEmpty()) {
                continue;
            }

            $organized[] = (object) [
                'category' => $category,
                'listings' => $listings,
            ];
        }

        return view('admin.trades.listings-approved', compact('organized'));
    }

    /**
     * Rejected trade listings organized by category.
     */
    public function rejectedListings()
    {
        $categories = \App\Models\Category::orderBy('name')->get();
        $organized = [];

        foreach ($categories as $category) {
            $listings = TradeListing::with(['user', 'images', 'category'])
                ->where('category_id', $category->id)
                ->where('status', 'rejected')
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($listings->isEmpty()) {
                continue;
            }

            $organized[] = (object) [
                'category' => $category,
                'listings' => $listings,
            ];
        }

        return view('admin.trades.listings-rejected', compact('organized'));
    }
}
