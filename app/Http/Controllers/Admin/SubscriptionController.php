<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display all subscriptions (by plan/member)
     */
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'plan'])->orderByDesc('created_at');

        if ($request->filled('plan')) {
            $query->whereHas('plan', fn ($q) => $q->where('slug', $request->plan));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->paginate(20);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }
}
