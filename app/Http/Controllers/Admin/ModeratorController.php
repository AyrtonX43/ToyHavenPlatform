<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ModeratorController extends Controller
{
    public function index()
    {
        $moderators = User::where('role', 'moderator')
            ->with('assignedBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.moderators.index', compact('moderators'));
    }

    public function create()
    {
        return view('admin.moderators.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => 'in:manage_orders,manage_disputes,view_reports,manage_sellers',
        ]);

        $moderator = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'moderator',
            'moderator_permissions' => $request->permissions ?? [],
            'moderator_assigned_at' => now(),
            'assigned_by' => Auth::id(),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Moderator created successfully.');
    }

    public function show($id)
    {
        $moderator = User::where('role', 'moderator')
            ->with(['assignedBy', 'moderatedDisputes'])
            ->findOrFail($id);

        return view('admin.moderators.show', compact('moderator'));
    }

    public function edit($id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        return view('admin.moderators.edit', compact('moderator'));
    }

    public function update(Request $request, $id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $moderator->id,
            'permissions' => 'array',
            'permissions.*' => 'in:manage_orders,manage_disputes,view_reports,manage_sellers',
        ]);

        $moderator->update([
            'name' => $request->name,
            'email' => $request->email,
            'moderator_permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Moderator updated successfully.');
    }

    public function destroy($id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        $moderator->update([
            'role' => 'customer',
            'moderator_permissions' => null,
        ]);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Moderator role removed successfully.');
    }
}
