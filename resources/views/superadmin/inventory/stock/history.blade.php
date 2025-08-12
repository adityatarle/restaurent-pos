@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Stock Transaction History for: {{ $inventoryItem->name }}</h1>
        <a href="{{ route('superadmin.inventory-items.index') }}" class="btn btn-secondary">Back to Inventory Items</a>
    </div>
    <p>Current Stock: <strong>{{ number_format($inventoryItem->current_stock, 2) }} {{ $inventoryItem->unit_of_measure }}</strong></p>


    <div class="card">
        <div class="card-body">
            @if($transactions->count() > 0)
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Cost/Unit</th>
                        <th>Supplier</th>
                        <th>Recorded By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                        <td class="text-capitalize">
                            <span class="badge bg-{{ $transaction->quantity > 0 ? 'success' : 'danger' }}">
                                {{ str_replace('_', ' ', $transaction->type) }}
                            </span>
                        </td>
                        <td class="text-end fw-bold {{ $transaction->quantity > 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($transaction->quantity, 2) }}
                        </td>
                        <td class="text-end">
                            {{ $transaction->cost_price_at_transaction ? '$'.number_format($transaction->cost_price_at_transaction, 2) : '-' }}
                        </td>
                        <td>{{ $transaction->supplier->name ?? '-' }}</td>
                        <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                        <td>{{ Str::limit($transaction->notes, 50) ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
            @else
            <div class="alert alert-info">No stock transactions found for this item.</div>
            @endif
        </div>
    </div>
</div>
@endsection