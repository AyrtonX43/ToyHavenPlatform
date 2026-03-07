@extends('layouts.toyshop')

@section('title', $plan->name . ' - Terms & Conditions')

@push('styles')
<style>
    .terms-content {
        font-size: 0.95rem;
        line-height: 1.7;
        max-width: 72ch;
    }
    .terms-content .terms-intro {
        margin-bottom: 1.75rem;
        font-weight: 500;
    }
    .terms-content .terms-section {
        margin-bottom: 1.75rem;
    }
    .terms-content .terms-h2,
    .terms-content h5 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        margin-top: 1rem;
        color: #0c4a6e;
    }
    .terms-content p {
        margin-bottom: 0.75rem;
    }
    .terms-content ul {
        margin: 0.5rem 0 1rem 1.25rem;
        padding-left: 0;
    }
    .terms-content li {
        margin-bottom: 0.4rem;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('membership.index') }}">Membership</a></li>
            <li class="breadcrumb-item active">{{ $plan->name }} Terms</li>
        </ol>
    </nav>
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">{{ $plan->name }} - Terms & Conditions</h4>
        </div>
        <div class="card-body">
            @php $termsContent = $plan->latestTerms()?->content; @endphp
            @if($termsContent)
                <div class="terms-content">{!! $termsContent !!}</div>
            @else
                @include('membership.terms-content')
            @endif
        </div>
    </div>
</div>
@endsection
