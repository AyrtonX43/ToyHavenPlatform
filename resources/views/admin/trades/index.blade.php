@extends('layouts.admin-new')

@section('title', 'Trades Management - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-3xl font-bold mb-6">Trades Management</h1>

                <!-- Filters -->
                <form method="GET" action="{{ route('admin.trades.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300">
                            <option value="">All Statuses</option>
                            <option value="pending_shipping" {{ request('status') == 'pending_shipping' ? 'selected' : '' }}>Pending Shipping</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="disputed" {{ request('status') == 'disputed' ? 'selected' : '' }}>Disputed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by listing title..." class="w-full rounded-md border-gray-300">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filter</button>
                        <a href="{{ route('admin.trades.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2">Clear</a>
                    </div>
                </form>

                <!-- Trades Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Listing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parties</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($trades as $trade)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $trade->id }}</td>
                                <td class="px-6 py-4 text-sm">{{ $trade->tradeListing->title }}</td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $trade->initiator->name }} ↔ {{ $trade->participant->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $trade->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $trade->status === 'disputed' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $trade->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ in_array($trade->status, ['pending_shipping', 'shipped', 'received']) ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                        {{ $trade->getStatusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $trade->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('admin.trades.show', $trade->id) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No trades found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $trades->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
