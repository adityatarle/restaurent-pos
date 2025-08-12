@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h2>{{ config('app.name', 'Restaurant') }} - Bill</h2>
                </div>
                <div class="card-body">
                    <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                    <p><strong>Table:</strong> {{ $order->restaurantTable->name }}</p>
                    <p><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
                    <p><strong>Waiter:</strong> {{ $order->waiter->name }}</p>
                    <p><strong>Customers:</strong> {{ $order->customer_count }}</p>
                    <hr>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems->where('status', '!=', 'cancelled') as $item)
                            <tr>
                                <td>{{ $item->menuItem->name }}
                                    @if($item->item_notes) <br><small><em>- {{ $item->item_notes }}</em></small> @endif
                                </td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->price_at_order, 2) }}</td>
                                <td class="text-end">${{ number_format($item->price_at_order * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">${{ number_format($order->orderItems->where('status', '!=', 'cancelled')->sum(fn($i) => $i->price_at_order * $i->quantity), 2) }}</td>
                            </tr>
                            {{-- Add Tax, Service Charge if applicable --}}
                            <tr>
                                <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end"><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    <hr>
                    <div class="text-center">
                        <p>Thank you for dining with us!</p>
                    </div>

                    <div class="mt-4 d-print-none">
                        @if($order->status != 'paid')
                        <form action="{{ route('reception.bill.pay', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="bi bi-cash-coin"></i> Mark as Paid & Free Table</button>
                        </form>
                        @else
                        <p class="alert alert-success text-center"><strong>ORDER PAID on {{ $order->completed_at->format('Y-m-d H:i A') }}</strong></p>
                        @endif
                        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print Bill</button>
                        <a href="{{ route('reception.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .d-print-none { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        body { font-size: 12pt; }
    }
</style>
@endpush