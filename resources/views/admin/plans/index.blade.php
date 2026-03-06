@extends('layouts.admin-new')

@section('title', 'Membership Plans')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Membership Plans</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Price</th>
                    <th>Interval</th>
                    <th>Analytics</th>
                    <th>Individual Seller</th>
                    <th>Business Seller</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td>{{ $plan->name }}</td>
                        <td><code>{{ $plan->slug }}</code></td>
                        <td>₱{{ number_format($plan->price, 2) }}</td>
                        <td>{{ $plan->interval }}</td>
                        <td>{{ $plan->has_analytics_dashboard ? 'Yes' : 'No' }}</td>
                        <td>{{ $plan->can_register_individual_seller ? 'Yes' : 'No' }}</td>
                        <td>{{ $plan->can_register_business_seller ? 'Yes' : 'No' }}</td>
                        <td>
                            <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
