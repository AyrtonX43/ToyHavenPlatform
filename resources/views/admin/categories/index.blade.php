@extends('layouts.admin-new')

@section('title', 'Category Management - ToyHaven')
@section('page-title', 'Category Management')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Categories</h5>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add New Category
        </a>
    </div>
    <div class="card-body">
        @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Image/Icon</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr>
                                <td class="align-middle">
                                    @if($category->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                    @elseif($category->getAnimatedIconPngUrl())
                                        <img src="{{ $category->getAnimatedIconPngUrl() }}" alt="{{ $category->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: contain;" title="Synced from category_animated_icons">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 40px; height: 40px;">
                                            <i class="bi {{ $category->getDisplayIcon() }} text-primary" style="font-size: 1.25rem;"></i>
                                        </div>
                                    @endif
                                </td>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ \Illuminate\Support\Str::limit($category->description ?? 'N/A', 50) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $category->products_count }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will fail if category has products.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info text-center">
                <p class="mb-0">No categories found. <a href="{{ route('admin.categories.create') }}">Create one now</a></p>
            </div>
        @endif
    </div>
</div>
@endsection
