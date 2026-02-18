@extends('layouts.toyshop')

@section('title', 'Order Tracking - ToyHaven')

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
            <li class="breadcrumb-item"><a href="{{ route('orders.show', $order->id) }}">{{ $order->order_number }}</a></li>
            <li class="breadcrumb-item active">Tracking</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Tracking - {{ $order->order_number }}</h5>
                </div>
                <div class="card-body">
                    <!-- Tracking Timeline -->
                    <div class="timeline">
                        @php
                            $stages = [
                                'order_placed' => ['Order Placed', 'Your order has been placed successfully'],
                                'payment_confirmed' => ['Payment Confirmed', 'Payment has been received and confirmed'],
                                'processing' => ['Processing', 'Seller is preparing your order'],
                                'packed' => ['Packed', 'Your order has been packed and ready for shipment'],
                                'shipped' => ['Shipped', 'Your order has been shipped'],
                                'in_transit' => ['In Transit', 'Your order is on the way'],
                                'out_for_delivery' => ['Out for Delivery', 'Your order is out for delivery'],
                                'delivered' => ['Delivered', 'Your order has been delivered'],
                            ];
                            
                            $trackingStatuses = $order->tracking->pluck('status')->toArray();
                        @endphp

                        @foreach($stages as $status => $info)
                            @php
                                $isCompleted = in_array($status, $trackingStatuses);
                                $isCurrent = $order->tracking->last() && $order->tracking->last()->status === $status;
                            @endphp
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    @if($isCompleted)
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-check"></i>
                                        </div>
                                    @elseif($isCurrent)
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-clock"></i>
                                        </div>
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-circle"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 {{ $isCompleted || $isCurrent ? 'text-primary' : 'text-muted' }}">
                                        {{ $info[0] }}
                                    </h6>
                                    <p class="text-muted mb-1">{{ $info[1] }}</p>
                                    @if($isCompleted)
                                        @php
                                            $trackingEntry = $order->tracking->where('status', $status)->first();
                                        @endphp
                                        @if($trackingEntry)
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>{{ $trackingEntry->created_at->format('M d, Y h:i A') }}
                                            </small>
                                            @if($trackingEntry->location)
                                                <p class="small mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $trackingEntry->location }}</p>
                                            @endif
                                            @if($trackingEntry->description)
                                                <p class="small mb-0 mt-1">{{ $trackingEntry->description }}</p>
                                            @endif
                                            @if($trackingEntry->estimated_delivery_date)
                                                <p class="small text-primary mb-0 mt-1">
                                                    <i class="bi bi-truck me-1"></i>Estimated Delivery: {{ $trackingEntry->estimated_delivery_date->format('M d, Y') }}
                                                </p>
                                            @endif
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($order->tracking_number)
                        <div class="alert alert-info mt-4">
                            <strong>Tracking Number:</strong> {{ $order->tracking_number }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-primary">Back to Order Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
