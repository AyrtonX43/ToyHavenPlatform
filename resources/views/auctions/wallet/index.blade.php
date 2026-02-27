@extends('layouts.toyshop')

@section('title', 'My Wallet - ToyHaven')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('auctions.index') }}">Auctions</a></li>
            <li class="breadcrumb-item active">Wallet</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4" style="background: linear-gradient(135deg, #0891b2, #06b6d4); border-radius: 16px; color: white;">
                <div class="mb-2" style="font-size: 3rem;"><i class="bi bi-wallet2"></i></div>
                <h5 class="fw-bold mb-1">ToyHaven Wallet</h5>
                <div style="font-size: 2.5rem; font-weight: 800;">₱{{ number_format($wallet->balance, 2) }}</div>
                <p class="mb-0 opacity-75 small">Available balance</p>
            </div>

            <div class="card border-0 shadow-sm mt-3 p-3">
                <h6 class="fw-bold mb-2">About Your Wallet</h6>
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i>Use for auction deposits</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-success me-1"></i>Skip deposit fees on future auctions</li>
                    <li><i class="bi bi-check-circle text-success me-1"></i>Wallet credits never expire</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-3 border-bottom">
                    <h5 class="fw-bold mb-0">Transaction History</h5>
                </div>
                <div class="card-body p-0">
                    @if($transactions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No transactions yet</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $tx)
                                        <tr>
                                            <td class="small">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                            <td><span class="badge bg-{{ $tx->amount >= 0 ? 'success' : 'danger' }}">{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</span></td>
                                            <td class="small">{{ $tx->description ?? '-' }}</td>
                                            <td class="text-end fw-semibold {{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $tx->amount >= 0 ? '+' : '' }}₱{{ number_format(abs($tx->amount), 2) }}
                                            </td>
                                            <td class="text-end small">₱{{ number_format($tx->balance_after, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">{{ $transactions->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
