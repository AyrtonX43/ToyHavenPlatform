<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Address;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['addresses', 'categoryPreferences']);
        
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id') // Only show top-level categories
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
        
        return view('profile.edit', [
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        $emailChanged = $user->email !== $request->email;
        
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Send verification email if email was changed
        if ($emailChanged && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return Redirect::route('profile.edit')
                ->with('status', 'profile-updated')
                ->with('email-verification-sent', 'A verification link has been sent to your new email address. Please verify it to complete the email change.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Store a new address.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:permanent,work,other',
            'label' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'is_default' => 'nullable',
        ]);

        $validated['user_id'] = $request->user()->id;
        
        // Normalize Philippine text to prevent encoding issues
        $validated['label'] = normalizePhilippineText($validated['label'] ?? null);
        $validated['address'] = normalizePhilippineText($validated['address']);
        $validated['city'] = normalizePhilippineText($validated['city']);
        $validated['province'] = normalizePhilippineText($validated['province']);
        
        // Handle is_default checkbox (unchecked checkboxes don't send a value)
        $isDefault = $request->has('is_default') && $request->is_default == '1';
        $validated['is_default'] = $isDefault;
        
        // If this is set as default, unset other defaults
        if ($isDefault) {
            Address::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        Address::create($validated);

        return Redirect::route('profile.edit')->with('success', 'Address added successfully!');
    }

    /**
     * Update an address.
     */
    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:permanent,work,other',
            'label' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'is_default' => 'nullable',
        ]);

        // Normalize Philippine text to prevent encoding issues
        $validated['label'] = normalizePhilippineText($validated['label'] ?? null);
        $validated['address'] = normalizePhilippineText($validated['address']);
        $validated['city'] = normalizePhilippineText($validated['city']);
        $validated['province'] = normalizePhilippineText($validated['province']);

        // Handle is_default checkbox (unchecked checkboxes don't send a value)
        $isDefault = $request->has('is_default') && $request->is_default == '1';
        $validated['is_default'] = $isDefault;

        // If this is set as default, unset other defaults
        if ($isDefault) {
            Address::where('user_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return Redirect::route('profile.edit')
            ->with('success', 'Address updated successfully!')
            ->with('edited_address_id', null);
    }

    /**
     * Delete an address.
     */
    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $address->delete();

        return Redirect::route('profile.edit')->with('success', 'Address deleted successfully!');
    }

    /**
     * Set an address as default.
     */
    public function setDefaultAddress(Request $request, Address $address): RedirectResponse
    {
        // Ensure the address belongs to the authenticated user
        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $address->setAsDefault();

        return Redirect::route('profile.edit')->with('success', 'Default address updated successfully!');
    }

    /**
     * Check if email exists (for real-time validation when changing email)
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower($request->email);
        $user = $request->user();

        // Check if email exists in users table, excluding current user
        $exists = \App\Models\User::where('email', $email)
            ->where('id', '!=', $user->id)
            ->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This email is already in use by another account.' : 'Email is available.',
        ]);
    }

    /**
     * Update user's category preferences
     */
    public function updateCategoryPreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $user = $request->user();
        
        // Sync user's category preferences (empty array clears all preferences)
        $categories = $request->categories ?? [];
        $user->categoryPreferences()->sync($categories);

        return Redirect::route('profile.edit')
            ->with('status', 'category-preferences-updated')
            ->with('success', 'Category preferences updated successfully!');
    }
}
