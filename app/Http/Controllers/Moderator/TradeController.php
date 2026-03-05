<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Trade::with(['tradeListing.user', 'initiator', 'participant']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tradeListing', fn ($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $trades = $query->orderByDesc('created_at')->paginate(20);
        return view('moderator.trades.index', compact('trades'));
    }

    public function show($id)
    {
        $trade = Trade::with([
            'tradeListing.images',
            'tradeListing.user',
            'tradeOffer',
            'initiator',
            'participant',
            'items',
            'dispute.reporter',
        ])->findOrFail($id);

        return view('moderator.trades.show', compact('trade'));
    }
}
