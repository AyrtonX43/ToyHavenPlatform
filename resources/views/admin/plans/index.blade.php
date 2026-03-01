@extends('layouts.admin-new')

@section('title', 'Plans - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-3xl font-bold mb-6">Membership Plans</h1>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Interval</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                                <tr>
                                    <td>{{ $plan->name }}</td>
                                    <td>₱{{ number_format($plan->price, 2) }}</td>
                                    <td>{{ $plan->interval }}</td>
                                    <td><span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">{{ $plan->is_active ? 'Yes' : 'No' }}</span></td>
                                    <td><a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
