@extends('layouts.admin-new')

@section('title', 'Create Category - ToyHaven')
@section('page-title', 'Create New Category')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Category Information</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Category Image</label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                <small class="text-muted">Optional. Shows as category thumbnail when set. Recommended: 400x400px.</small>
                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Category Icon</label>
                <select name="icon" class="form-select @error('icon') is-invalid @enderror">
                    <option value="">Auto (from category name)</option>
                    <option value="toy" {{ old('icon') == 'toy' ? 'selected' : '' }}>🎲 Toy</option>
                    <option value="puzzle" {{ old('icon') == 'puzzle' ? 'selected' : '' }}>🧩 Puzzle</option>
                    <option value="dice-5" {{ old('icon') == 'dice-5' ? 'selected' : '' }}>🎲 Board Games</option>
                    <option value="person-standing" {{ old('icon') == 'person-standing' ? 'selected' : '' }}>🦸 Action Figures</option>
                    <option value="person-standing-dress" {{ old('icon') == 'person-standing-dress' ? 'selected' : '' }}>👧 Dolls</option>
                    <option value="heart" {{ old('icon') == 'heart' ? 'selected' : '' }}>🧸 Plush</option>
                    <option value="book" {{ old('icon') == 'book' ? 'selected' : '' }}>📚 Educational</option>
                    <option value="sun" {{ old('icon') == 'sun' ? 'selected' : '' }}>☀️ Outdoor</option>
                    <option value="trophy" {{ old('icon') == 'trophy' ? 'selected' : '' }}>🏆 Sports</option>
                    <option value="brush" {{ old('icon') == 'brush' ? 'selected' : '' }}>🎨 Arts & Crafts</option>
                    <option value="truck" {{ old('icon') == 'truck' ? 'selected' : '' }}>🚗 Vehicles</option>
                    <option value="bricks" {{ old('icon') == 'bricks' ? 'selected' : '' }}>🧱 Building Blocks</option>
                    <option value="gem" {{ old('icon') == 'gem' ? 'selected' : '' }}>⭐ Collectibles</option>
                    <option value="cpu" {{ old('icon') == 'cpu' ? 'selected' : '' }}>🎮 Electronics</option>
                    <option value="controller" {{ old('icon') == 'controller' ? 'selected' : '' }}>🕹️ Video Games</option>
                    <option value="robot" {{ old('icon') == 'robot' ? 'selected' : '' }}>🤖 Robotic</option>
                    <option value="music-note-beamed" {{ old('icon') == 'music-note-beamed' ? 'selected' : '' }}>🎵 Musical</option>
                    <option value="beaker" {{ old('icon') == 'beaker' ? 'selected' : '' }}>🧪 Science/STEM</option>
                    <option value="car-front" {{ old('icon') == 'car-front' ? 'selected' : '' }}>🚙 Diecast/RC</option>
                    <option value="card-image" {{ old('icon') == 'card-image' ? 'selected' : '' }}>🃏 Trading Cards</option>
                    <option value="house-door" {{ old('icon') == 'house-door' ? 'selected' : '' }}>🏠 Pretend Play</option>
                </select>
                <small class="text-muted">Optional. Used when no image is set. Choose "Auto" to match from category name.</small>
                @error('icon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
