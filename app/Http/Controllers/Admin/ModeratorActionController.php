<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModeratorAction;
use App\Models\User;
use Illuminate\Http\Request;

class ModeratorActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = ModeratorAction::with(['moderator', 'actionable']);

        if ($request->filled('moderator_id')) {
            $query->where('moderator_id', $request->moderator_id);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('actionable_type')) {
            $query->where('actionable_type', $request->actionable_type);
        }

        $actions = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        $moderators = User::whereIn('role', ['moderator', 'admin'])->orderBy('name')->get();

        $actionTypes = ModeratorAction::select('action_type')->distinct()->pluck('action_type');

        return view('admin.moderator-actions.index', compact('actions', 'moderators', 'actionTypes'));
    }
}
