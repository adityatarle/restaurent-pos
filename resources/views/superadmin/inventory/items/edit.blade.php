@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Inventory Item: {{ $item->name }}</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.inventory-items.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $item->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category', $item->category) }}">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="unit_of_measure" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('unit_of_measure') is-invalid @enderror" id="unit_of_measure" name="unit_of_measure" value="{{ old('unit_of_measure', $item->unit_of_measure) }}" placeholder="e.g., kg, pcs, ltr" required>
                        @error('unit_of_measure')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="average_cost_price" class="form-label">Average Cost Price (per unit)</label>
                        <input type="number" step="0.01" class="form-control @error('average_cost_price') is-invalid @enderror" id="average_cost_price" name="average_cost_price" value="{{ old('average_cost_price', $item->average_cost_price) }}">
                        @error('average_cost_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">This is usually updated automatically on purchases.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="reorder_level" class="form-label">Reorder Level (optional)</label>
                        <input type="number" step="0.01" class="form-control @error('reorder_level') is-invalid @enderror" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level) }}">
                        @error('reorder_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <hr>
                <p><strong>Current Stock:</strong> {{ number_format($item->current_stock, 2) }} {{ $item->unit_of_measure }}
                    <a href="{{ route('superadmin.stock.create', $item->id) }}" class="btn btn-outline-success btn-sm ms-2">Manage Stock</a>
                    <a href="{{ route('superadmin.stock.history', $item->id) }}" class="btn btn-outline-info btn-sm ms-2">View History</a>
                </p>


                <button type="submit" class="btn btn-primary">Update Item</button>
                <a href="{{ route('superadmin.inventory-items.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection