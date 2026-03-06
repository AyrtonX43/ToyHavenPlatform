@extends('layouts.admin-new')

@section('title', 'Subscriptions')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Subscriptions</h1>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Period Start</th>
                    <th>Period End</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscriptions as $sub)
                    <tr>
                        <td>{{ $sub->user->name ?? 'N/A' }} ({{ $sub->user->email }})</td>
                        <td>{{ $sub->plan->name }}</td>
                        <td><span class="badge bg-{{ $sub->status === 'active' ? 'success' : ($sub->status === 'pending' ? 'warning' : 'secondary') }}">{{ $sub->status }}</span></td>
                        <td>{{ $sub->current_period_start?->format('M d, Y') ?? '-' }}</td>
                        <td>{{ $sub->current_period_end?->format('M d, Y') ?? '-' }}</td>
                        <td>{{ $sub->created_at->format('M d, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $subscriptions->links() }}
</div>
@endsection
