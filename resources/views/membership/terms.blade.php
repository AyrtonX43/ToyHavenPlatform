@extends('layouts.toyshop')

@section('title', $plan->name . ' - Terms & Conditions')

@push('styles')
<style>
    .terms-content { line-height: 1.65; color: #334155; }
    .terms-content .terms-section { margin-bottom: 1.5rem; }
    .terms-content .terms-section:last-child { margin-bottom: 0; }
    .terms-content h5 { font-size: 1rem; font-weight: 600; color: #0f172a; margin-bottom: 0.5rem; }
    .terms-content h6 { font-size: 0.95rem; font-weight: 600; color: #1e293b; margin-bottom: 0.4rem; }
    .terms-content p { margin-bottom: 0.5rem; }
    .terms-content ul, .terms-content ol { margin-bottom: 0.5rem; padding-left: 1.25rem; }
    .terms-content li { margin-bottom: 0.25rem; }
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
