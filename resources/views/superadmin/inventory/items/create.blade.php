@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Inventory Item</h1>

    <form action="{{ route('superadmin.inventory-items.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category') }}">
                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="unit_of_measure" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('unit_of_measure') is-invalid @enderror" id="unit_of_measure" name="unit_of_measure" value="{{ old('unit_of_measure') }}" placeholder="e.g., kg, pcs, ltr" required>
                @error('unit_of_measure') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="average_cost_price" class="form-label">Average Cost Price (per unit)</label>
                <input type="number" step="0.01" class="form-control @error('average_cost_price') is-invalid @enderror" id="average_cost_price" name="average_cost_price" value="{{ old('average_cost_price') }}">
                @error('average_cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="reorder_level" class="form-label">Reorder Level (optional)</label>
                <input type="number" step="0.01" class="form-control @error('reorder_level') is-invalid @enderror" id="reorder_level" name="reorder_level" value="{{ old('reorder_level') }}">
                @error('reorder_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <hr>
        <h5>Opening Stock (Optional - for new items)</h5>
         <div class="row">
            <div class="col-md-6 mb-3">
                <label for="opening_stock" class="form-label">Opening Stock Quantity</label>
                <input type="number" step="0.001" class="form-control @error('opening_stock') is-invalid @enderror" id="opening_stock" name="opening_stock" value="{{ old('opening_stock') }}">
                @error('opening_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="opening_stock_cost" class="form-label">Cost Price for Opening Stock (per unit)</label>
                <input type="number" step="0.01" class="form-control @error('opening_stock_cost') is-invalid @enderror" id="opening_stock_cost" name="opening_stock_cost" value="{{ old('opening_stock_cost') }}">
                @error('opening_stock_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="form-text text-muted">If not provided, Average Cost Price will be used.</small>
            </div>
        </div>


        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Save Item</button>
        <a href="{{ route('superadmin.inventory-items.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection