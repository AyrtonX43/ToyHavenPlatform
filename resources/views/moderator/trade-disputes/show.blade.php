@extends('layouts.admin-new')
@section('title', 'Dispute #' . $dispute->id . ' - Moderator')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Dispute #{{ $dispute->id }}</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Trade:</strong> #{{ $dispute->trade_id }}</p>
            <p><strong>Reporter:</strong> {{ $dispute->reporter->name }}
                @if(!$dispute->reporter->isTradeSuspended())
                <form action="{{ route('moderator.trade-users.suspend', $dispute->reporter->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspend this user from trade?');">
                    @csrf
                    <input type="hidden" name="reason" value="Dispute #{{ $dispute->id }}">
                    <button type="submit" class="btn btn-sm btn-warning ms-2">Suspend from Trade</button>
                </form>
                @else
                <span class="badge bg-warning">Trade suspended</span>
                @endif
            </p>
            @php $other = $dispute->trade->initiator_id === $dispute->reporter_id ? $dispute->trade->participant : $dispute->trade->initiator; @endphp
            <p><strong>Other party:</strong> {{ $other->name }}
                @if(!$other->isTradeSuspended())
                <form action="{{ route('moderator.trade-users.suspend', $other->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Suspend this user from trade?');">
                    @csrf
                    <input type="hidden" name="reason" value="Dispute #{{ $dispute->id }}">
                    <button type="submit" class="btn btn-sm btn-warning ms-2">Suspend from Trade</button>
                </form>
                @else
                <span class="badge bg-warning">Trade suspended</span>
                @endif
            </p>
            <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}</p>
            <p><strong>Description:</strong> {{ $dispute->description }}</p>
            @if(!$dispute->assigned_to || $dispute->assigned_to === auth()->id())
            <form action="{{ route('moderator.trade-disputes.assign', $dispute->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">Assign to Me</button>
            </form>
            @endif
            @if($dispute->assigned_to === auth()->id())
            <form action="{{ route('moderator.trade-disputes.resolve', $dispute->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="resolution" value="completed">
                <textarea name="notes" class="form-control mb-2" rows="2" placeholder="Resolution notes"></textarea>
                <button type="submit" class="btn btn-success">Complete Trade</button>
            </form>
            <form action="{{ route('moderator.trade-disputes.resolve', $dispute->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="resolution" value="cancelled">
                <textarea name="notes" class="form-control d-none" rows="1"></textarea>
                <button type="submit" class="btn btn-danger">Cancel Trade</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
