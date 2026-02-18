<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of admin users.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin');
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
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
        
        $admins = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        return view('admin.admins.create');
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
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
        ], [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password_confirmation.same' => 'The password confirmation does not match.',
        ]);

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'email_verified_at' => now(), // Auto-verify admin accounts
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account created successfully. Email: ' . $admin->email);
    }

    /**
     * Display the specified admin user.
     */
    public function show($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        
        // Get admin activity stats
        $stats = [
            'created_at' => $admin->created_at,
            'last_login' => $admin->updated_at, // You might want to track this separately
            'total_actions' => 0, // You can implement activity logging later
        ];
        
        return view('admin.admins.show', compact('admin', 'stats'));
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        
        // Prevent editing yourself (optional - you can remove this if you want)
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('info', 'You can edit your own profile from the profile page.');
        }
        
        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin user.
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        
        // Prevent editing yourself (optional)
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.admins.index')
                ->with('info', 'You can update your own profile from the profile page.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'password' => [
                'nullable',
                'confirmed', 
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'name.regex' => 'The name field may only contain letters and spaces.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account updated successfully.');
    }

    /**
     * Remove the specified admin user (soft delete or deactivate).
     */
    public function destroy($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        
        // Prevent deleting yourself
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting if it's the last admin
        $adminCount = User::where('role', 'admin')->count();
        if ($adminCount <= 1) {
            return back()->with('error', 'Cannot delete the last admin account.');
        }

        // Instead of deleting, you might want to deactivate or change role
        // For now, we'll just change the role to customer
        $admin->update(['role' => 'customer']);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin account removed successfully.');
    }
}
