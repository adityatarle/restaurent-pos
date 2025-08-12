@foreach($tables as $table)
<div class="col-md-3 mb-4">
    <div class="card h-100 shadow-sm border-0 table-card status-{{ $table->status }} @if($table->currentOrder && $table->currentOrder->user_id == Auth::id()) my-table @endif">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>{{ $table->name }}</strong>
            <span class="badge text-capitalize {{ $table->status === 'occupied' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $table->status }}</span>
        </div>
        <div class="card-body">
            @if($table->status == 'occupied' && $table->currentOrder)
                <div class="small text-muted mb-2">Capacity: {{ $table->capacity }}</div>
                <div class="mb-2">
                    <div>Order #{{ $table->currentOrder->id }}</div>
                    <div>Customers: {{ $table->currentOrder->customer_count }}</div>
                    <div>Waiter: {{ $table->currentOrder->waiter->name }}</div>
                </div>
                @if($table->currentOrder->user_id == Auth::id() || Auth::user()->isSuperAdmin())
                    <a href="{{ route('waiter.orders.show', $table->currentOrder->id) }}" class="btn btn-sm btn-primary w-100 mb-2">View / Edit</a>
                    <form action="{{ route('waiter.tables.request_bill', $table->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-outline-success w-100" type="submit"><i class="bi bi-receipt"></i> Request Bill</button>
                    </form>
                @else
                    <button class="btn btn-sm btn-secondary w-100" disabled>Occupied by {{ $table->currentOrder->waiter->name }}</button>
                @endif
            @elseif($table->status == 'available')
                <div class="small text-muted mb-2">Capacity: {{ $table->capacity }}</div>
                <button type="button" class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#assignTableModal-{{ $table->id }}">
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
@endforeach