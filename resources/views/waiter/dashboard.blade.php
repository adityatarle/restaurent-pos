@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Waiter Dashboard - Table View</h1>
    <div class="row">
        @forelse($tables as $table)
        <div class="col-md-3 mb-4">
            <div class="card h-100 table-card status-{{ $table->status }} @if($table->currentOrder && $table->currentOrder->user_id == Auth::id()) my-table @endif">
                <div class="card-header">
                    <strong>{{ $table->name }}</strong> (Capacity: {{ $table->capacity }})
                </div>
                <div class="card-body">
                    <h5 class="card-title text-capitalize">Status: {{ $table->status }}</h5>
                    @if($table->status == 'occupied' && $table->currentOrder)
                        <p>Order #{{ $table->currentOrder->id }}</p>
                        <p>Customers: {{ $table->currentOrder->customer_count }}</p>
                        <p>Waiter: {{ $table->currentOrder->waiter->name }}</p>
                        @if($table->currentOrder->user_id == Auth::id() || Auth::user()->isSuperAdmin())
                            <a href="{{ route('waiter.orders.show', $table->currentOrder->id) }}" class="btn btn-sm btn-info">View/Edit Order</a>
                        @else
                            <button class="btn btn-sm btn-secondary" disabled>Occupied by {{ $table->currentOrder->waiter->name }}</button>
                        @endif
                    @elseif($table->status == 'available')
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignTableModal-{{ $table->id }}">
                            Assign Table
                        </button>
                    @else
                         <p class="text-muted">Currently {{ $table->status }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Assign Table Modal -->
        <div class="modal fade" id="assignTableModal-{{ $table->id }}" tabindex="-1" aria-labelledby="assignTableModalLabel-{{ $table->id }}" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="{{ route('waiter.tables.assign', $table->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                  <h5 class="modal-title" id="assignTableModalLabel-{{ $table->id }}">Assign {{ $table->name }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="customer_count-{{ $table->id }}" class="form-label">Number of People</label>
                    <input type="number" class="form-control" id="customer_count-{{ $table->id }}" name="customer_count" min="1" value="1" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Assign & Start Order</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        @empty
        <p>No tables configured yet. Please ask an Admin to add tables.</p>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-card.status-available .card-header { background-color: #d1e7dd; border-color: #badbcc; } /* Greenish */
    .table-card.status-occupied .card-header { background-color: #f8d7da; border-color: #f5c2c7; } /* Reddish */
    .table-card.status-reserved .card-header { background-color: #fff3cd; border-color: #ffecb5; } /* Yellowish */
    .table-card.my-table { border: 2px solid blue; }
</style>
@endpush