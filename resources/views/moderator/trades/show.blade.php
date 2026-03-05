@extends('layouts.admin-new')
@section('title', 'Trade #' . $trade->id . ' - Moderator')
@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Trade #{{ $trade->id }}</h1>
    <div class="card">
        <div class="card-body">
            <p><strong>Status:</strong> {{ $trade->getStatusLabel() }}</p>
            <p><strong>Listing:</strong> {{ $trade->tradeListing->title }}</p>
            <p><strong>Initiator:</strong> {{ $trade->initiator->name }}</p>
            <p><strong>Participant:</strong> {{ $trade->participant->name }}</p>
        </div>
    </div>
</div>
@endsection
