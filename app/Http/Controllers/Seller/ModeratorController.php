<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerModerator;
use App\Models\User;
use Illuminate\Http\Request;

class ModeratorController extends Controller
{
    public function index()
    {
        $seller = auth()->user()->getSellerForDashboard();
        if (!$seller) {
            return redirect()->route('seller.dashboard');
        }

        // Only owner can manage moderators
        if (!auth()->user()->seller || auth()->user()->seller->id !== $seller->id) {
            abort(403, 'Only the business owner can manage moderators.');
        }

        $moderators = $seller->moderators;
        return view('seller.moderators.index', compact('seller', 'moderators'));
    }

    public function store(Request $request)
    {
        $seller = auth()->user()->getSellerForDashboard();
        if (!$seller || !auth()->user()->seller || auth()->user()->seller->id !== $seller->id) {
            abort(403, 'Only the business owner can add moderators.');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'permissions' => 'required|array',
            'permissions.*' => 'in:' . implode(',', SellerModerator::validPermissions()),
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot add yourself as a moderator.');
        }
        if ($user->seller && $user->seller->id === $seller->id) {
            return back()->with('error', 'This user is already the business owner.');
        }
        if (SellerModerator::where('seller_id', $seller->id)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'This user is already a moderator.');
        }

        SellerModerator::create([
            'seller_id' => $seller->id,
            'user_id' => $user->id,
            'permissions' => $request->permissions,
        ]);

        return back()->with('success', 'Moderator added successfully. They can now log in and access the seller dashboard based on their permissions.');
    }

    public function update(Request $request, SellerModerator $moderator)
    {
        $seller = auth()->user()->getSellerForDashboard();
        if (!$seller || !auth()->user()->seller || auth()->user()->seller->id !== $seller->id || $moderator->seller_id !== $seller->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'in:' . implode(',', SellerModerator::validPermissions()),
        ]);

        $moderator->update(['permissions' => $request->permissions]);
        return back()->with('success', 'Moderator permissions updated.');
    }

    public function destroy(SellerModerator $moderator)
    {
        $seller = auth()->user()->getSellerForDashboard();
        if (!$seller || !auth()->user()->seller || auth()->user()->seller->id !== $seller->id || $moderator->seller_id !== $seller->id) {
            abort(403, 'Unauthorized.');
        }

        $moderator->delete();
        return back()->with('success', 'Moderator removed.');
    }
}
