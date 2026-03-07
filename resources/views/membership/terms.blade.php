@extends('layouts.toyshop')

@section('title', $plan->name . ' - Terms & Conditions')

@push('styles')
<style>
    .terms-content {
        text-align: left;
        line-height: 1.7;
        font-size: 0.95rem;
        color: #334155;
    }
    .terms-content h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 1.25rem 0 0.5rem 0;
        padding-bottom: 0.25rem;
    }
    .terms-content h5:first-child { margin-top: 0; }
    .terms-content ul {
        margin: 0 0 0 1.25rem;
        padding: 0;
        list-style: disc;
    }
    .terms-content li { margin-bottom: 0.4rem; }
    .terms-content p { margin: 0 0 0.75rem 0; }
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
        <div class="card-body px-4 py-4">
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
