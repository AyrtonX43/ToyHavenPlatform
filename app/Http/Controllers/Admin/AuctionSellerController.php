<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionSellerVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionSellerController extends Controller
{
    public function index(Request $request)
    {
        $query = AuctionSellerVerification::with(['user', 'seller'])
            ->where('status', 'approved');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sql) use ($q) {
                $sql->where('auction_business_name', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('seller', fn ($s) => $s->where('business_name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('type')) {
            $query->where('seller_type', $request->type);
        }

        if ($request->filled('sync')) {
            if ($request->sync === 'synced') {
                $query->whereNotNull('seller_id');
            } else {
                $query->whereNull('seller_id');
            }
        }

        if ($request->filled('suspended')) {
            $query->where('is_suspended', $request->suspended === '1');
        }

        $sellers = $query->orderByDesc('verified_at')->paginate(20);

        return view('admin.auctions.sellers.index', compact('sellers'));
    }

    public function show(AuctionSellerVerification $seller)
    {
        $seller->load(['user', 'seller', 'documents', 'verifiedByUser', 'suspendedByUser']);

        $auctionCount = 0;
        if (class_exists(\App\Models\Auction::class)) {
            $auctionCount = \App\Models\Auction::where('user_id', $seller->user_id)->count();
        }

        return view('admin.auctions.sellers.show', compact('seller', 'auctionCount'));
    }

    public function updateBusinessName(Request $request, AuctionSellerVerification $seller)
    {
        $request->validate([
            'auction_business_name' => 'required|string|max:255',
        ]);

        $seller->update([
            'auction_business_name' => $request->auction_business_name,
        ]);

        return back()->with('success', 'Auction business name updated successfully.');
    }

    public function suspend(Request $request, AuctionSellerVerification $seller)
    {
        $request->validate([
            'suspension_reason' => 'required|string|max:1000',
        ]);

        $seller->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspension_reason' => $request->suspension_reason,
            'suspended_by' => Auth::id(),
        ]);

        return back()->with('success', 'Auction seller has been suspended.');
    }

    public function activate(AuctionSellerVerification $seller)
    {
        $seller->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspension_reason' => null,
            'suspended_by' => null,
        ]);

        return back()->with('success', 'Auction seller has been activated.');
    }
}
