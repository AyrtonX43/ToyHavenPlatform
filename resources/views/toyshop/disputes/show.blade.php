@extends('layouts.toyshop')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('disputes.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Disputes
            </a>
        </div>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Dispute #{{ $dispute->id }}</h1>
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                @if($dispute->status === 'open') bg-yellow-100 text-yellow-800
                @elseif($dispute->status === 'investigating') bg-blue-100 text-blue-800
                @elseif($dispute->status === 'resolved') bg-green-100 text-green-800
                @else bg-gray-100 text-gray-800
                @endif">
                {{ $dispute->getStatusLabel() }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-2">Order Information</h3>
                <p class="text-sm text-gray-600">Order #{{ $dispute->order->order_number }}</p>
                <p class="text-sm text-gray-600">Total: ₱{{ number_format($dispute->order->total, 2) }}</p>
                <a href="{{ route('orders.show', $dispute->order->id) }}" class="text-blue-600 hover:text-blue-800 text-sm mt-2 inline-block">
                    View Order →
                </a>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-2">Issue Type</h3>
                <p class="text-sm">{{ $dispute->getTypeLabel() }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold mb-2">Reported On</h3>
                <p class="text-sm">{{ $dispute->created_at->format('F d, Y') }}</p>
                <p class="text-xs text-gray-500">{{ $dispute->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-semibold text-lg mb-4">Description</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $dispute->description }}</p>
        </div>

        @if($dispute->evidence_images && count($dispute->evidence_images) > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="font-semibold text-lg mb-4">Evidence Photos</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($dispute->evidence_images as $image)
                <a href="{{ Storage::url($image) }}" target="_blank" class="block">
                    <img src="{{ Storage::url($image) }}" alt="Evidence" class="w-full h-32 object-cover rounded border hover:opacity-75 transition">
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($dispute->assignedTo)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="font-semibold text-lg mb-2">Assigned Moderator</h2>
            <p class="text-sm text-blue-800">{{ $dispute->assignedTo->name }}</p>
            <p class="text-xs text-blue-600">This dispute is being reviewed by a moderator.</p>
        </div>
        @endif

        @if($dispute->isResolved())
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
            <h2 class="font-semibold text-lg mb-2">Resolution</h2>
            <p class="text-sm text-green-800 mb-2">
                <strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $dispute->resolution_type)) }}
            </p>
            <p class="text-sm text-green-800 mb-2">
                <strong>Resolved by:</strong> {{ $dispute->resolvedBy->name }}
            </p>
            <p class="text-sm text-green-800 mb-2">
                <strong>Resolved on:</strong> {{ $dispute->resolved_at->format('F d, Y h:i A') }}
            </p>
            @if($dispute->resolution_notes)
            <div class="mt-4 pt-4 border-t border-green-200">
                <p class="text-sm font-medium text-green-900 mb-1">Notes:</p>
                <p class="text-sm text-green-800 whitespace-pre-line">{{ $dispute->resolution_notes }}</p>
            </div>
            @endif
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-lg mb-4">Order Items</h2>
            @foreach($dispute->order->items as $item)
            <div class="flex items-center py-3 border-b last:border-b-0">
                <div class="flex-1">
                    <p class="font-medium">{{ $item->product_name }}</p>
                    <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }} × ₱{{ number_format($item->price, 2) }}</p>
                </div>
                <p class="font-semibold">₱{{ number_format($item->subtotal, 2) }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
