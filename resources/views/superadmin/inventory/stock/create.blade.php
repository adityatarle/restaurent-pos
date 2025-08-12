@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Stock for: {{ $inventoryItem->name }}</h1>
    <p>Current Stock: <strong>{{ number_format($inventoryItem->current_stock, 2) }} {{ $inventoryItem->unit_of_measure }}</strong></p>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.stock.store', $inventoryItem->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Transaction Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">-- Select Type --</option>
                            <option value="purchase" {{ old('type') == 'purchase' ? 'selected' : '' }}>Purchase (Stock In)</option>
                            <option value="manual_adjustment_in" {{ old('type') == 'manual_adjustment_in' ? 'selected' : '' }}>Manual Adjustment (Stock In)</option>
                            <option value="wastage" {{ old('type') == 'wastage' ? 'selected' : '' }}>Wastage (Stock Out)</option>
                            <option value="manual_adjustment_out" {{ old('type') == 'manual_adjustment_out' ? 'selected' : '' }}>Manual Adjustment (Stock Out)</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="transaction_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity (in {{ $inventoryItem->unit_of_measure }}) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" required placeholder="0.000">
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3" id="cost_price_section" style="{{ old('type') == 'purchase' ? '' : 'display:none;' }}">
                        <label for="cost_price_at_transaction" class="form-label">Cost Price per Unit (for Purchase)</label>
                        <input type="number" step="0.01" class="form-control @error('cost_price_at_transaction') is-invalid @enderror" id="cost_price_at_transaction" name="cost_price_at_transaction" value="{{ old('cost_price_at_transaction') }}" placeholder="0.00">
                        @error('cost_price_at_transaction')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3" id="supplier_section" style="{{ old('type') == 'purchase' ? '' : 'display:none;' }}">
                    <label for="supplier_id" class="form-label">Supplier (for Purchase)</label>
                    <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                        <option value="">-- Select Supplier (Optional) --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Save Stock Transaction</button>
                <a href="{{ route('superadmin.inventory-items.index') }}" class="btn btn-secondary">Back to Inventory Items</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const costPriceSection = document.getElementById('cost_price_section');
    const supplierSection = document.getElementById('supplier_section');
    const costPriceInput = document.getElementById('cost_price_at_transaction');
    const supplierInput = document.getElementById('supplier_id');

    function togglePurchaseFields() {
        if (typeSelect.value === 'purchase') {
            costPriceSection.style.display = 'block';
            supplierSection.style.display = 'block';
            costPriceInput.required = true; // Make required for purchase
            // supplierInput.required = true; // Make required if needed for purchase
        } else {
            costPriceSection.style.display = 'none';
            supplierSection.style.display = 'none';
            costPriceInput.required = false;
            // supplierInput.required = false;
        }
    }

    typeSelect.addEventListener('change', togglePurchaseFields);
    togglePurchaseFields(); // Initial check on page load
});
</script>
@endpush