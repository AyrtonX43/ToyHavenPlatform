<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Http\Request;

class TradeController extends Controller
{
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
            $query->whereHas('tradeListing', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $trades = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('moderator.trades.index', compact('trades'));
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

        return view('moderator.trades.show', compact('trade'));
    }
}
