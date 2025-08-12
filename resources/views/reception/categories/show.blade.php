@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Category: {{ $category->name }}</h1>
            @if($category->description)
                <p class="text-muted">{{ $category->description }}</p>
            @endif
        </div>
        <div>
            <a href="{{ route('reception.menu-items.create', ['category_id' => $category->id]) }}" class="btn btn-success">Add Menu Item to this Category</a>
            <a href="{{ route('reception.categories.edit', $category->id) }}" class="btn btn-warning">Edit Category</a>
            <a href="{{ route('reception.categories.index') }}" class="btn btn-secondary">Back to Categories</a>
        </div>
    </div>


    <div class="card">
        <div class="card-header">
            Menu Items in {{ $category->name }} ({{ $menuItems->count() }})
        </div>
        <div class="card-body">
            @if($menuItems->count() > 0)
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menuItems as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>{{ Str::limit($item->description, 50) ?: '-' }}</td>
                        <td>
                            @if($item->is_available)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('reception.menu-items.edit', $item->id) }}" class="btn btn-sm btn-warning" title="Edit Item">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            {{-- Delete form for menu item would typically be on menu item's own management page or here if desired --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
             <div class="mt-3">
                {{-- If you paginate $menuItems in the controller --}}
                {{-- {{ $menuItems->links() }} --}}
            </div>
            @else
            <div class="alert alert-info">No menu items found in this category.
                <a href="{{ route('reception.menu-items.create', ['category_id' => $category->id]) }}" class="btn btn-sm btn-link">Add one now?</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection