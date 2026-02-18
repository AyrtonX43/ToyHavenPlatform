@extends('layouts.admin')

@section('title', 'Edit Category - ToyHaven')
@section('page-title', 'Edit Category: ' . $category->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Category Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Category Image</label>
                @if($category->image)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded" style="max-height: 80px;">
                        <label class="ms-2">
                            <input type="checkbox" name="remove_image" value="1"> Remove image
                        </label>
                    </div>
                @endif
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                <small class="text-muted">Optional. Shows as category thumbnail. Recommended: 400x400px.</small>
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Category Icon</label>
                @if($category->getAnimatedIconPngUrl())
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <img src="{{ $category->getAnimatedIconPngUrl() }}" alt="" class="rounded" style="width: 32px; height: 32px; object-fit: contain;">
                        <small class="text-muted">This category uses the synced icon from config (welcome/profile).</small>
                    </div>
                @endif
                <select name="icon" class="form-select @error('icon') is-invalid @enderror">
                    <option value="">Auto (from category name)</option>
                    <option value="toy" {{ old('icon', $category->icon) == 'toy' ? 'selected' : '' }}>🎲 Toy</option>
                    <option value="puzzle" {{ old('icon', $category->icon) == 'puzzle' ? 'selected' : '' }}>🧩 Puzzle</option>
                    <option value="dice-5" {{ old('icon', $category->icon) == 'dice-5' ? 'selected' : '' }}>🎲 Board Games</option>
                    <option value="person-standing" {{ old('icon', $category->icon) == 'person-standing' ? 'selected' : '' }}>🦸 Action Figures</option>
                    <option value="person-standing-dress" {{ old('icon', $category->icon) == 'person-standing-dress' ? 'selected' : '' }}>👧 Dolls</option>
                    <option value="heart" {{ old('icon', $category->icon) == 'heart' ? 'selected' : '' }}>🧸 Plush</option>
                    <option value="book" {{ old('icon', $category->icon) == 'book' ? 'selected' : '' }}>📚 Educational</option>
                    <option value="sun" {{ old('icon', $category->icon) == 'sun' ? 'selected' : '' }}>☀️ Outdoor</option>
                    <option value="trophy" {{ old('icon', $category->icon) == 'trophy' ? 'selected' : '' }}>🏆 Sports</option>
                    <option value="brush" {{ old('icon', $category->icon) == 'brush' ? 'selected' : '' }}>🎨 Arts & Crafts</option>
                    <option value="truck" {{ old('icon', $category->icon) == 'truck' ? 'selected' : '' }}>🚗 Vehicles</option>
                    <option value="bricks" {{ old('icon', $category->icon) == 'bricks' ? 'selected' : '' }}>🧱 Building Blocks</option>
                    <option value="gem" {{ old('icon', $category->icon) == 'gem' ? 'selected' : '' }}>⭐ Collectibles</option>
                    <option value="cpu" {{ old('icon', $category->icon) == 'cpu' ? 'selected' : '' }}>🎮 Electronics</option>
                    <option value="controller" {{ old('icon', $category->icon) == 'controller' ? 'selected' : '' }}>🕹️ Video Games</option>
                    <option value="robot" {{ old('icon', $category->icon) == 'robot' ? 'selected' : '' }}>🤖 Robotic</option>
                    <option value="music-note-beamed" {{ old('icon', $category->icon) == 'music-note-beamed' ? 'selected' : '' }}>🎵 Musical</option>
                    <option value="beaker" {{ old('icon', $category->icon) == 'beaker' ? 'selected' : '' }}>🧪 Science/STEM</option>
                    <option value="car-front" {{ old('icon', $category->icon) == 'car-front' ? 'selected' : '' }}>🚙 Diecast/RC</option>
                    <option value="card-image" {{ old('icon', $category->icon) == 'card-image' ? 'selected' : '' }}>🃏 Trading Cards</option>
                    <option value="house-door" {{ old('icon', $category->icon) == 'house-door' ? 'selected' : '' }}>🏠 Pretend Play</option>
                </select>
                <small class="text-muted">Optional. Used when no image is set.</small>
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
