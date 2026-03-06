<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ModeratorUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of moderator users.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'moderator');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $moderators = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.moderators.index', compact('moderators'));
    }

    /**
     * Show the form for creating a new moderator user.
     */
    public function create()
    {
        return view('admin.moderators.create');
    }

    /**
     * Store a newly created moderator user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'password_confirmation' => ['required', 'same:password'],
            'moderator_permissions' => ['nullable', 'array'],
            'moderator_permissions.*' => 'in:auctions_view,auctions_moderate,auction_payments_moderate,auction_sellers_moderate,plans_manage',
        ], [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password_confirmation.same' => 'The password confirmation does not match.',
        ]);

        $moderator = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'moderator',
            'moderator_permissions' => $request->input('moderator_permissions', []),
        ]);
        $moderator->email_verified_at = now();
        $moderator->save();

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Trade moderator account created successfully. They can log in at ' . url('/login') . ' with email: ' . $moderator->email);
    }

    /**
     * Display the specified moderator user.
     */
    public function show($id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        $stats = [
            'created_at' => $moderator->created_at,
            'last_activity' => $moderator->updated_at,
        ];

        return view('admin.moderators.show', compact('moderator', 'stats'));
    }

    /**
     * Show the form for editing the specified moderator user.
     */
    public function edit($id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        return view('admin.moderators.edit', compact('moderator'));
    }

    /**
     * Update the specified moderator user.
     */
    public function update(Request $request, $id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $moderator->id],
            'password' => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
            'moderator_permissions' => ['nullable', 'array'],
            'moderator_permissions.*' => 'in:auctions_view,auctions_moderate,auction_payments_moderate,auction_sellers_moderate,plans_manage',
        ], [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'moderator_permissions' => $request->input('moderator_permissions', []),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $moderator->update($updateData);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Moderator account updated successfully.');
    }

    /**
     * Remove the moderator account (change role to customer).
     */
    public function destroy($id)
    {
        $moderator = User::where('role', 'moderator')->findOrFail($id);

        $moderator->update(['role' => 'customer']);

        return redirect()->route('admin.moderators.index')
            ->with('success', 'Moderator account removed successfully.');
    }
}
