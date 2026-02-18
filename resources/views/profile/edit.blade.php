@extends('layouts.toyshop')

@section('title', 'Profile - ToyHaven')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold reveal"><i class="bi bi-person-circle me-2"></i>Profile</h2>
            <p class="text-muted reveal">Manage your account information and settings.</p>
                </div>
            </div>

    <div class="row">
        <div class="col-md-8">
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.category-preferences-form')
            @include('profile.partials.addresses-form')
            @if(!auth()->user()->google_id)
                @include('profile.partials.update-password-form')
            @endif
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
