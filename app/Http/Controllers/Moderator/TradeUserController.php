<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TradeUserController extends Controller
{
    public function suspendFromTrade(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own trade access.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $user->update([
            'trade_suspended' => true,
            'trade_suspended_at' => now(),
            'trade_suspension_reason' => $request->reason,
            'trade_suspended_by' => auth()->id(),
        ]);

        \App\Models\ModeratorAction::log(auth()->id(), 'trade_user_suspended', $user, 'User suspended from trade', [
            'user_id' => $user->id,
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'User suspended from trade.');
    }

    public function unsuspendFromTrade($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'trade_suspended' => false,
            'trade_suspended_at' => null,
            'trade_suspension_reason' => null,
            'trade_suspended_by' => null,
        ]);

        \App\Models\ModeratorAction::log(auth()->id(), 'trade_user_unsuspended', $user, 'User trade access restored', [
            'user_id' => $user->id,
        ]);

        return back()->with('success', 'User trade access restored.');
    }
}
