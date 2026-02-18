<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryPreferenceController extends Controller
{
    /**
     * Show the category selection page for new users
     */
    public function show()
    {
        // Check if user has already selected categories
        if (Auth::user()->hasSelectedCategories()) {
            return redirect()->route('suggested-products');
        }

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id') // Only show top-level categories
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        return view('auth.welcome-categories', compact('categories'));
    }

    /**
     * Store user's category preferences
     */
    public function store(Request $request)
    {
        $request->validate([
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ]);

        $user = Auth::user();
        
        // Sync user's category preferences
        $user->categoryPreferences()->sync($request->categories);

        return redirect()->route('suggested-products')
            ->with('success', 'Thank you! We\'ve saved your preferences. Here are some products we think you\'ll love!');
    }

    /**
     * Skip category selection and redirect to homepage
     */
    public function skip()
    {
        // User has skipped category selection, redirect to homepage
        return redirect()->route('home')
            ->with('info', 'You can always update your category preferences later in your User Settings under your profile.');
    }
}
