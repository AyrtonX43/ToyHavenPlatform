@extends('layouts.admin-new')

@section('title', 'Auction Payments')

@section('content')
<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Auction Payments</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Auction</th>
                    <th>Winner</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Delivery</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                    <tr>
                        <td>{{ $p->auction->title ?? 'N/A' }}</td>
                        <td>{{ $p->winner->name ?? 'N/A' }}</td>
                        <td>₱{{ number_format($p->amount, 2) }}</td>
                        <td><span class="badge bg-{{ $p->status === 'held' ? 'warning' : ($p->status === 'released' ? 'success' : 'secondary') }}">{{ $p->status }}</span></td>
                        <td>{{ $p->delivery_status ?? '-' }}</td>
                        <td>
                            @if($p->canRelease())
                                <form action="{{ route('admin.auction-payments.release', $p) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Release</button>
                                </form>
                            @endif
                            @if(in_array($p->status, ['pending', 'held']))
                                <form action="{{ route('admin.auction-payments.refund', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Refund this payment?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Refund</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
