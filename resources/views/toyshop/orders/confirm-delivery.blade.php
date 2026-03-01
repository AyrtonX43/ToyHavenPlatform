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

        <h1 class="text-3xl font-bold mb-6">Confirm Delivery</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-semibold text-lg mb-2">Order #{{ $order->order_number }}</h2>
            <p class="text-gray-600 text-sm mb-4">Please upload a photo as proof of delivery</p>
            
            <div class="border-t pt-4">
                <h3 class="font-medium mb-2">Order Items:</h3>
                @foreach($order->items as $item)
                <div class="flex items-center py-2">
                    <div class="flex-1">
                        <p class="font-medium">{{ $item->product_name }}</p>
                        <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                    </div>
                    <p class="font-semibold">₱{{ number_format($item->subtotal, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <form action="{{ route('orders.confirm-delivery.store', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Proof of Delivery Photo <span class="text-red-500">*</span></label>
                <input type="file" name="proof_image" accept="image/*" required 
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Upload a clear photo showing the delivered package (max 5MB)</p>
                @error('proof_image')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Notes (Optional)</label>
                <textarea name="notes" rows="3" 
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                    placeholder="Any additional comments about the delivery..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Maximum 500 characters</p>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Note:</strong> By confirming delivery, you acknowledge that you have received the order in good condition. 
                    You will be able to review the product after confirmation.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-medium transition">
                    Confirm Delivery
                </button>
                <a href="{{ route('orders.show', $order->id) }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 font-medium transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
