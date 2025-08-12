@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Table Details: {{ $table->name }}</h1>
        <div>
            <a href="{{ route('superadmin.tables.edit', $table->id) }}" class="btn btn-warning">Edit Table</a>
            <a href="{{ route('superadmin.tables.index') }}" class="btn btn-secondary">Back to Tables List</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Table Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $table->id }}</p>
                    <p><strong>Name:</strong> {{ $table->name }}</p>
                    <p><strong>Capacity:</strong> {{ $table->capacity }} people</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="text-capitalize">{{ $table->status }}</span></p>
                    <p><strong>Visual Coordinates:</strong> {{ $table->visual_coordinates ?: 'Not set' }}</p>
                    <p><strong>Created At:</strong> {{ $table->created_at->format('Y-m-d H:i:s') }}</p>
                    <p><strong>Last Updated:</strong> {{ $table->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- You could add more details here, like current order if occupied, etc. --}}
    @if($table->status == 'occupied' && $table->currentOrder)
        <div class="card mt-4">
            <div class="card-header">
                Current Order Details (Order #{{ $table->currentOrder->id }})
            </div>
            <div class="card-body">
                <p><strong>Waiter:</strong> {{ $table->currentOrder->waiter->name ?? 'N/A' }}</p>
                <p><strong>Customers:</strong> {{ $table->currentOrder->customer_count }}</p>
                <p><strong>Total Amount:</strong> ${{ number_format($table->currentOrder->total_amount, 2) }}</p>
                <p><strong>Order Status:</strong> <span class="text-capitalize">{{ $table->currentOrder->status }}</span></p>
                <a href="{{ route('waiter.orders.show', $table->currentOrder->id) }}" class="btn btn-info btn-sm">View Full Order</a>
                {{-- This route 'waiter.orders.show' might need adjustment based on your actual route for viewing orders by SuperAdmin --}}
            </div>
        </div>
    @endif

</div>
@endsection