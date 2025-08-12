@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Inventory Items</h1>
    <div class="mb-3">
        <a href="{{ route('superadmin.inventory-items.create') }}" class="btn btn-primary">Add New Item</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th>Unit</th>
                <th>Current Stock</th>
                <th>Avg. Cost</th>
                <th>Reorder Lvl</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category ?: '-' }}</td>
                <td>{{ $item->unit_of_measure }}</td>
                <td>{{ number_format($item->current_stock, 2) }}</td>
                <td>${{ number_format($item->average_cost_price, 2) }}</td>
                <td>{{ $item->reorder_level ? number_format($item->reorder_level, 2) : '-' }}</td>
                <td>
                    <a href="{{ route('superadmin.stock.create', $item->id) }}" class="btn btn-sm btn-success" title="Manage Stock"><i class="bi bi-box-seam"></i> Stock</a>
                    <a href="{{ route('superadmin.stock.history', $item->id) }}" class="btn btn-sm btn-info" title="Stock History"><i class="bi bi-clock-history"></i> History</a>
                    <a href="{{ route('superadmin.inventory-items.edit', $item->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('superadmin.inventory-items.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No inventory items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection