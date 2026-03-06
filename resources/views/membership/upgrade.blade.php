@extends('layouts.toyshop')

@section('title', 'Upgrade to ' . $newPlan->name . ' - ToyHaven')

@section('content')
<div class="container py-4">
    <h1 class="h2 fw-bold mb-4">Upgrade to {{ $newPlan->name }}</h1>

    <div class="card mb-4">
        <div class="card-body">
            <p class="mb-2">Your current plan: <strong>{{ $subscription->plan->name }}</strong></p>
            <p class="mb-2">New plan: <strong>{{ $newPlan->name }}</strong> – ₱{{ number_format($newPlan->price, 0) }}/{{ $newPlan->interval === 'yearly' ? 'year' : 'month' }}</p>
            <p class="mb-2 text-muted">Prorated credit from current plan: ₱{{ number_format($proratedCredit, 2) }}</p>
            <p class="h5 mb-0">Amount due now: <strong class="text-primary">₱{{ number_format($amountDue, 2) }}</strong></p>
        </div>
    </div>

    @if($amountDue > 0)
        <form action="{{ route('membership.process-upgrade') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $newPlan->id }}">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-credit-card me-2"></i>Pay ₱{{ number_format($amountDue, 2) }} now
            </button>
        </form>
        <span class="mx-2">or</span>
    @endif

    <form action="{{ route('membership.schedule-upgrade') }}" method="POST" class="d-inline">
        @csrf
        <input type="hidden" name="plan_id" value="{{ $newPlan->id }}">
        <button type="submit" class="btn btn-outline-primary">
            <i class="bi bi-calendar-check me-2"></i>Upgrade at next renewal
        </button>
    </form>

    <p class="mt-3 mb-0">
        <a href="{{ route('membership.manage') }}" class="text-muted">Back to membership</a>
    </p>
</div>
@endsection
