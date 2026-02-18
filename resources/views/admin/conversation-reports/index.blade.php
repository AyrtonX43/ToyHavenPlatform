@extends('layouts.admin')

@section('title', 'Conversation Reports - ToyHaven')
@section('page-title', 'Conversation Reports')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Trade chat reports (with snapshot)</h5>
        <form method="GET" action="{{ route('admin.conversation-reports.index') }}" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="width: auto;">
                <option value="">All statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </form>
    </div>
    <div class="card-body p-0">
        @if(session('success'))
            <div class="alert alert-success mb-0 rounded-0">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Conversation</th>
                        <th>Reporter</th>
                        <th>Reason (excerpt)</th>
                        <th>Status</th>
                        <th>Reported at</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $report->id }}</td>
                            <td>
                                @if($report->conversation)
                                    #{{ $report->conversation_id }}
                                    @if($report->conversation->user1 && $report->conversation->user2)
                                        <br><small class="text-muted">{{ $report->conversation->user1->name }} & {{ $report->conversation->user2->name }}</small>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $report->reporter->name ?? '—' }}</td>
                            <td>{{ Str::limit($report->reason, 60) }}</td>
                            <td><span class="badge bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'resolved' ? 'success' : 'secondary') }}">{{ $report->status }}</span></td>
                            <td>{{ $report->created_at->format('M j, Y g:i A') }}</td>
                            <td><a href="{{ route('admin.conversation-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">View snapshot</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No reports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
        <div class="card-footer">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
