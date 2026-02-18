@extends('layouts.toyshop')

@section('title', 'Report Conversation - ToyHaven Trading')

@section('content')
<div class="container my-4" style="max-width: 560px;">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.conversations.index') }}">Messages</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trading.conversations.show', $conversation) }}">Chat</a></li>
            <li class="breadcrumb-item active">Report</li>
        </ol>
    </nav>

    <div class="card shadow-sm border">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-flag me-2"></i>Report this conversation</h5>
            <p class="text-muted small">A snapshot of the conversation will be saved for admin review. Please describe the issue.</p>

            <form action="{{ route('trading.conversations.report', $conversation) }}" method="post">
                @csrf
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason / Description <span class="text-danger">*</span></label>
                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" maxlength="2000" required placeholder="Describe what happened...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Max 2000 characters. The current conversation will be captured as a snapshot for admin.</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger">Submit report</button>
                    <a href="{{ route('trading.conversations.show', $conversation) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
