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
            @include('membership.terms-content')
        </div>
    </div>
</div>
@endsection
