@extends('layouts.admin-new')

@section('title', 'Conversation Report #' . $report->id . ' - ToyHaven')
@section('page-title', 'Report #' . $report->id . ' (Conversation snapshot)')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Report details</h5>
            </div>
            <div class="card-body">
                <p><strong>Reporter:</strong> {{ $report->reporter->name }} (ID: {{ $report->reporter_id }})</p>
                <p><strong>Reported user:</strong> {{ $reportedUser?->name ?? 'N/A' }} (ID: {{ $reportedUser?->id ?? 'N/A' }})</p>
                <p><strong>Reported user penalty count:</strong> {{ $reportedUser?->trade_penalty_count ?? 0 }}</p>
                <p><strong>Reported at:</strong> {{ $report->created_at->format('M j, Y g:i A') }}</p>
                <p><strong>Reason:</strong></p>
                <div class="bg-light rounded p-3 mb-2">{{ $report->reason }}</div>
                @if(!empty($report->proof_images) && is_array($report->proof_images))
                    <p><strong>Proof images:</strong></p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @foreach($report->proof_images as $path)
                            <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener"><img src="{{ asset('storage/' . $path) }}" alt="Proof" style="max-width: 120px; max-height: 100px; object-fit: cover; border-radius: 8px;"></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Conversation snapshot (at time of report)</h5>
            </div>
            <div class="card-body">
                @php $snapshot = $report->snapshot ?? []; @endphp
                @if(empty($snapshot))
                    <p class="text-muted mb-0">No messages in snapshot.</p>
                @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($snapshot as $msg)
                            <div class="border rounded p-3 {{ ($msg['sender_id'] ?? 0) == $report->reporter_id ? 'bg-primary bg-opacity-10 border-primary' : 'bg-light' }}">
                                <div class="small text-muted mb-1">{{ $msg['sender_name'] ?? 'User' }} · {{ \Carbon\Carbon::parse($msg['created_at'] ?? now())->format('M j, g:i A') }}</div>
                                @if(!empty($msg['message']))
                                    <div class="mb-2">{{ $msg['message'] }}</div>
                                @endif
                                @if(!empty($msg['attachments']))
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($msg['attachments'] as $att)
                                            @php $url = $att['url'] ?? asset('storage/' . ($att['file_path'] ?? '')); @endphp
                                            @if(str_starts_with($att['file_type'] ?? '', 'image/'))
                                                <img src="{{ $url }}" alt="" style="max-width: 150px; max-height: 120px; object-fit: cover; border-radius: 8px;">
                                            @elseif(str_starts_with($att['file_type'] ?? '', 'video/'))
                                                <video src="{{ $url }}" controls style="max-width: 200px; max-height: 150px; border-radius: 8px;"></video>
                                            @else
                                                <a href="{{ $url }}" target="_blank" class="small">Attachment</a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Update report</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.conversation-reports.update', $report) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin notes</label>
                        <textarea name="admin_notes" class="form-control" rows="4">{{ $report->admin_notes }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apply penalty to reported user</label>
                        <select name="penalty" class="form-select">
                            <option value="">— No penalty —</option>
                            <option value="5">First penalty: Suspend 5 days (no trade access)</option>
                            <option value="30">Second penalty: Suspend 30 days (no trade access)</option>
                            <option value="ban">Third penalty: Ban from Trade</option>
                        </select>
                        <div class="form-text">Current penalty count: {{ $reportedUser?->trade_penalty_count ?? 0 }}</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
