<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $wallet->transactions()->paginate(20);

        return view('auctions.wallet.index', compact('wallet', 'transactions'));
    }

    public function keepDeposit(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'auction_id' => 'nullable|exists:auctions,id',
        ]);

        $wallet->credit(
            (float) $request->amount,
            'credit',
            'Deposit kept from auction' . ($request->auction_id ? " #{$request->auction_id}" : ''),
        );

        return redirect()->route('auctions.wallet.index')
            ->with('success', '₱' . number_format($request->amount, 2) . ' has been added to your ToyHaven Wallet.');
    }
}
