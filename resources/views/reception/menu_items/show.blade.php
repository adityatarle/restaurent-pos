@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Menu Item: {{ $menuItem->name }}</h1>
        <a href="{{ route('reception.menu-items.index') }}" class="btn btn-secondary">Back to List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    @if($menuItem->image_path)
                        <img src="{{ asset('storage/' . $menuItem->image_path) }}" alt="{{ $menuItem->name }}" class="img-fluid rounded mb-3" style="max-height: 300px; object-fit: cover; width: 100%;">
                    @else
                        <img src="https://via.placeholder.com/300x300.png?text=No+Image" alt="No image available" class="img-fluid rounded mb-3">
                    @endif
                </div>
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $menuItem->name }}</dd>

                        <dt class="col-sm-3">Category:</dt>
                        <dd class="col-sm-9">{{ $menuItem->category->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Price:</dt>
                        <dd class="col-sm-9">${{ number_format($menuItem->price, 2) }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $menuItem->description ?? 'No description provided.' }}</dd>

                        <dt class="col-sm-3">Available:</dt>
                        <dd class="col-sm-9">{{ $menuItem->is_available ? 'Yes' : 'No' }}</dd>

                        <dt class="col-sm-3">Created At:</dt>
                        <dd class="col-sm-9">{{ $menuItem->created_at->format('M d, Y H:i A') }}</dd>

                        <dt class="col-sm-3">Last Updated:</dt>
                        <dd class="col-sm-9">{{ $menuItem->updated_at->format('M d, Y H:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('reception.menu-items.edit', $menuItem->id) }}" class="btn btn-primary">Edit Item</a>
            {{-- You can add a delete button here if needed, similar to the index page --}}
            {{-- <form action="{{ route('reception.menu-items.destroy', $menuItem->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form> --}}
        </div>
    </div>
</div>
@endsection