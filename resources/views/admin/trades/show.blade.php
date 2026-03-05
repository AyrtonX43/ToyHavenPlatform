@extends('layouts.admin-new')
@section('title', 'Trade #' . $trade->id . ' - Admin')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Trade #{{ $trade->id }}</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Status:</strong> {{ $trade->getStatusLabel() }}</p>
            <p><strong>Listing:</strong> {{ $trade->tradeListing->title }}</p>
            <p><strong>Initiator:</strong> {{ $trade->initiator->name }}</p>
            <p><strong>Participant:</strong> {{ $trade->participant->name }}</p>
            @if($trade->status === 'disputed' && $trade->dispute)
            <hr>
            <h5>Resolve Dispute</h5>
            <form action="{{ route('admin.trades.resolve-dispute', $trade->id) }}" method="POST" class="mt-2">
                @csrf
                <textarea name="notes" class="form-control mb-2" rows="2" placeholder="Notes"></textarea>
                <button type="submit" name="resolution" value="completed" class="btn btn-success">Complete Trade</button>
            </form>
            <form action="{{ route('admin.trades.resolve-dispute', $trade->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="resolution" value="cancelled">
                <textarea name="notes" class="form-control mb-2 d-none" rows="2"></textarea>
                <button type="submit" class="btn btn-danger">Cancel Trade</button>
            </form>
            @endif
            @if(!in_array($trade->status, ['completed', 'cancelled']))
            <form action="{{ route('admin.trades.cancel', $trade->id) }}" method="POST" class="d-inline mt-2" onsubmit="return confirm('Cancel this trade?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Cancel Trade</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
