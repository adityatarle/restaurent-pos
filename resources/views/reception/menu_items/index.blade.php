@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Menu Items</h1>
    <div class="mb-3">
        <a href="{{ route('reception.menu-items.create') }}" class="btn btn-primary">Add New Menu Item</a>
    </div>

    {{-- Optional: Filter by Category --}}
    <form method="GET" action="{{ route('reception.menu-items.index') }}" class="row g-3 mb-3 align-items-center">
        <div class="col-auto">
            <label for="category_id_filter" class="col-form-label">Filter by Category:</label>
        </div>
        <div class="col-auto">
            <select name="category_id" id="category_id_filter" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info">Filter</button>
            <a href="{{ route('reception.menu-items.index') }}" class="btn btn-secondary ms-2">Clear Filter</a>
        </div>
    </form>


    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
    @if ($message = Session::get('error'))
        <div class="alert alert-danger">
            <p>{{ $message }}</p>
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Available?</th>
                <th width="280px">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($menuItems as $i => $item)
            <tr>
                <td>{{ $i + 1 + (($menuItems->currentPage() - 1) * $menuItems->perPage()) }}</td>
                <td>
                    @if($item->image_path)
                        <img src="{{ asset(path: 'storage/' . $item->image_path) }}" alt="{{ $item->name }}" width="50" height="50" style="object-fit: cover;">
                    @else
                        <img src="https://via.placeholder.com/50" alt="No image" width="50" height="50">
                    @endif
                </td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category->name ?? 'N/A' }}</td>
                <td>${{ number_format($item->price, 2) }}</td>
                <td>{{ $item->is_available ? 'Yes' : 'No' }}</td>
                <td>
                    <form action="{{ route('reception.menu-items.destroy',$item->id) }}" method="POST">
                        <a class="btn btn-info btn-sm" href="{{ route('reception.menu-items.show',$item->id) }}">Show</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('reception.menu-items.edit',$item->id) }}">Edit</a>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this menu item?')">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No menu items found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {!! $menuItems->links() !!}
</div>
@endsection