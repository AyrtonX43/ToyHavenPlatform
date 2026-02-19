@extends('layouts.admin')

@section('title', 'Subscriptions - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-3xl font-bold mb-6">Subscriptions</h1>

                <form method="GET" action="{{ route('admin.subscriptions.index') }}" class="mb-4">
                    <select name="status" class="form-select d-inline-block w-auto">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                    <button type="submit" class="btn btn-secondary ms-2">Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Period End</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscriptions as $sub)
                                <tr>
                                    <td>{{ $sub->id }}</td>
                                    <td>{{ $sub->user->name ?? 'N/A' }}<br><small class="text-muted">{{ $sub->user->email ?? '' }}</small></td>
                                    <td>{{ $sub->plan->name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-{{ $sub->status === 'active' ? 'success' : 'secondary' }}">{{ $sub->status }}</span></td>
                                    <td>{{ $sub->current_period_end?->format('M d, Y') ?? '-' }}</td>
                                    <td>{{ $sub->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">No subscriptions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $subscriptions->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
