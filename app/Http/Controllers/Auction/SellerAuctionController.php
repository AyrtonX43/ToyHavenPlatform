<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerAuctionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'membership']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.verification.index')
                ->with('error', 'You need approved auction seller verification to list auctions.');
        }

        $query = Auction::where('user_id', $user->id)->with('images', 'category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $auctions = $query->orderByDesc('created_at')->paginate(12);

        return view('auctions.seller.index', compact('auctions'));
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.verification.index')
                ->with('error', 'You need approved auction seller verification to list auctions.');
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $userProducts = UserProduct::where('user_id', $user->id)->where('status', 'available')->get();

        return view('auctions.seller.create', compact('categories', 'userProducts'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.verification.index')
                ->with('error', 'You need approved auction seller verification to list auctions.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'starting_bid' => ['required', 'numeric', 'min:1'],
            'bid_increment' => ['required', 'numeric', 'min:1'],
            'start_at' => ['nullable', 'date', 'after_or_equal:now'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $verification = $user->auctionSellerVerification;
        $sellerId = $verification && $verification->type === 'business' ? $verification->seller_id : null;
        $sellerType = $verification?->type;

        $auction = DB::transaction(function () use ($user, $validated, $sellerId, $sellerType, $request) {
            $auction = Auction::create([
                'user_id' => $user->id,
                'seller_id' => $sellerId,
                'seller_type' => $sellerType,
                'category_id' => $validated['category_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'starting_bid' => $validated['starting_bid'],
                'bid_increment' => $validated['bid_increment'],
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'],
                'status' => Auction::STATUS_DRAFT,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $i => $file) {
                    $path = $file->store('auctions/' . $auction->id, 'public');
                    AuctionImage::create([
                        'auction_id' => $auction->id,
                        'image_path' => $path,
                        'is_primary' => $i === 0,
                    ]);
                }
            }

            return $auction;
        });

        return redirect()->route('auctions.seller.edit', $auction)
            ->with('success', 'Auction created. Add more details and submit for approval when ready.');
    }

    public function edit(Auction $auction)
    {
        $this->authorize('update', $auction);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $userProducts = UserProduct::where('user_id', Auth::id())->where('status', 'available')->get();

        return view('auctions.seller.edit', compact('auction', 'categories', 'userProducts'));
    }

    public function update(Request $request, Auction $auction)
    {
        $this->authorize('update', $auction);

        if (! $auction->isDraft()) {
            return redirect()->back()->with('error', 'Only draft auctions can be edited.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'starting_bid' => ['required', 'numeric', 'min:1'],
            'bid_increment' => ['required', 'numeric', 'min:1'],
            'start_at' => ['nullable', 'date', 'after_or_equal:now'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => [Rule::exists('auction_images', 'id')->where('auction_id', $auction->id)],
        ]);

        DB::transaction(function () use ($auction, $validated, $request) {
            $auction->update([
                'category_id' => $validated['category_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'starting_bid' => $validated['starting_bid'],
                'bid_increment' => $validated['bid_increment'],
                'start_at' => $validated['start_at'] ?? null,
                'end_at' => $validated['end_at'],
            ]);

            if ($request->filled('remove_images')) {
                $toRemove = AuctionImage::whereIn('id', $request->remove_images)->where('auction_id', $auction->id)->get();
                foreach ($toRemove as $img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }

            if ($request->hasFile('images')) {
                $hasPrimary = $auction->images()->where('is_primary', true)->exists();
                foreach ($request->file('images') as $file) {
                    $path = $file->store('auctions/' . $auction->id, 'public');
                    AuctionImage::create([
                        'auction_id' => $auction->id,
                        'image_path' => $path,
                        'is_primary' => ! $hasPrimary,
                    ]);
                    $hasPrimary = true;
                }
            }
        });

        return redirect()->route('auctions.seller.edit', $auction)->with('success', 'Auction updated.');
    }

    public function submit(Auction $auction)
    {
        $this->authorize('update', $auction);

        if (! $auction->isDraft()) {
            return redirect()->back()->with('error', 'Only draft auctions can be submitted.');
        }

        if ($auction->images()->count() < 1) {
            return redirect()->back()->with('error', 'Add at least one image before submitting.');
        }

        $auction->update(['status' => Auction::STATUS_PENDING_APPROVAL]);

        return redirect()->route('auctions.seller.index')->with('success', 'Auction submitted for approval.');
    }

    public function destroy(Auction $auction)
    {
        $this->authorize('delete', $auction);

        if (! $auction->isDraft() && ! $auction->isCancelled()) {
            return redirect()->back()->with('error', 'Only draft or cancelled auctions can be deleted.');
        }

        foreach ($auction->images as $img) {
            Storage::disk('public')->delete($img->image_path);
        }
        $auction->delete();

        return redirect()->route('auctions.seller.index')->with('success', 'Auction deleted.');
    }
}
