<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AccountBannedNotification;
use App\Notifications\AccountSuspendedNotification;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Roles available for the organizer (must match users.role values).
     */
    protected function getRoleOrganizerRoles(): array
    {
        return ['customer', 'seller', 'premium', 'admin'];
    }

    public function index(Request $request)
    {
        $query = User::with('seller');

        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }

        // Filter by banned status
        if ($request->has('banned')) {
            $query->where('is_banned', $request->banned == '1');
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Date filter
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Role organizer: counts per role (for current filters except role)
        $baseQuery = User::query();
        if ($request->has('banned')) {
            $baseQuery->where('is_banned', $request->banned == '1');
        }
        if ($request->search) {
            $baseQuery->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->date_from) {
            $baseQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $baseQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $roleCounts = [
            'all' => (clone $baseQuery)->count(),
        ];
        foreach ($this->getRoleOrganizerRoles() as $role) {
            $roleCounts[$role] = (clone $baseQuery)->where('role', $role)->count();
        }

        return view('admin.users.index', compact('users', 'roleCounts'));
    }

    public function show($id)
    {
        $user = User::with([
            'seller', 
            'orders' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            }, 
            'reviews' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            }
        ])->findOrFail($id);
        
        // Statistics
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'total_reviews' => $user->reviews()->count(),
            'wishlist_items' => $user->wishlists()->count(),
        ];
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    public function ban(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Prevent banning yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot ban your own account.');
        }
        
        // Prevent banning admin users
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot ban admin users.');
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
            'report_id' => 'nullable|exists:reports,id',
        ]);

        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
            'ban_reason' => $request->reason,
            'banned_by' => auth()->id(),
            'related_report_id' => $request->report_id,
        ]);

        // Send notification
        $user->notify(new AccountBannedNotification($request->reason, $request->report_id));
        
        return back()->with('success', 'User banned and notified via email.');
    }

    public function unban($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
            'ban_reason' => null,
            'banned_by' => null,
            'related_report_id' => null,
        ]);
        
        return back()->with('success', 'User unbanned.');
    }
}
