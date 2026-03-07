@extends('layouts.toyshop')

@section('title', $plan->name . ' - Terms & Conditions')

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Membership</a></li>
            <li class="breadcrumb-item active">{{ $plan->name }} Terms</li>
        </ol>
    </nav>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ $plan->name }} - Terms & Conditions</h4>
        </div>
        <div class="card-body">
            @php $termsContent = $plan->latestTerms()?->content; @endphp
            @if($termsContent)
                <div class="terms-content" style="white-space: pre-wrap;">{{ $termsContent }}</div>
            @else
                <p class="text-muted mb-0">No terms available. Please contact support.</p>
            @endif
        </div>
    </div>
</div>
@endsection
