@extends('layouts.toyshop')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('orders.show', $order->id) }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Order
            </a>
        </div>

        <h1 class="text-3xl font-bold mb-6">Report an Issue</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-semibold text-lg mb-2">Order #{{ $order->order_number }}</h2>
            <p class="text-gray-600 text-sm mb-4">Describe the issue with your order</p>
            
            <div class="border-t pt-4">
                <h3 class="font-medium mb-2">Order Details:</h3>
                <p class="text-sm text-gray-600">Seller: {{ $order->seller->business_name }}</p>
                <p class="text-sm text-gray-600">Order Date: {{ $order->created_at->format('F d, Y') }}</p>
                <p class="text-sm text-gray-600">Total: ₱{{ number_format($order->total, 2) }}</p>
            </div>
        </div>

        <form action="{{ route('disputes.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Issue Type <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select issue type</option>
                    <option value="not_received">Not Received</option>
                    <option value="damaged">Damaged Item</option>
                    <option value="wrong_item">Wrong Item</option>
                    <option value="incomplete">Incomplete Order</option>
                    <option value="other">Other</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="6" required 
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="Please provide detailed information about the issue (minimum 20 characters)..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 20 characters, maximum 1000 characters</p>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Evidence Photos (Optional)</label>
                <input type="file" name="evidence_images[]" accept="image/*" multiple 
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Upload photos showing the issue (max 5MB each, multiple files allowed)</p>
                @error('evidence_images.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
                <p class="text-sm text-yellow-800">
                    <strong>Important:</strong> A moderator will review your dispute and contact both you and the seller. 
                    Please provide as much detail as possible to help resolve the issue quickly.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 font-medium transition">
                    Submit Report
                </button>
                <a href="{{ route('orders.show', $order->id) }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 font-medium transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
