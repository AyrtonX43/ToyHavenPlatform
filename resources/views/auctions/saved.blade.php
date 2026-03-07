@extends('layouts.toyshop')

@section('title', 'Saved Auctions - ToyHaven')

@section('content')
<div class="container py-4">
    <h2>Saved Auctions</h2>
    @if($saved->count() > 0)
        <div class="row g-4 mt-2">
            @foreach($saved as $s)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if($s->auction->images->first())
                            <img src="{{ asset('storage/' . $s->auction->images->first()->path) }}" class="card-img-top" style="height: 150px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h6>{{ $s->auction->title }}</h6>
                            <span class="badge bg-{{ $s->auction->status === 'live' ? 'success' : 'secondary' }}">{{ $s->auction->status }}</span>
                            <a href="{{ route('auctions.show', $s->auction) }}" class="btn btn-sm btn-primary mt-2">View</a>
                            <form action="{{ route('auctions.unsave', $s->auction) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Unsave</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        {{ $saved->links() }}
    @else
        <p class="text-muted">No saved auctions.</p>
    @endif
</div>
@endsection
