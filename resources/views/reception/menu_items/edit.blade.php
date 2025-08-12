@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Menu Item: {{ $menuItem->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reception.menu-items.update', $menuItem->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="name" class="form-label"><strong>Name:</strong></label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $menuItem->name) }}" placeholder="Enter menu item name" required>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label"><strong>Category:</strong></label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $menuItem->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label"><strong>Price:</strong></label>
                    <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $menuItem->price) }}" placeholder="0.00" step="0.01" min="0" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label"><strong>Description:</strong> (Optional)</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter a brief description">{{ old('description', $menuItem->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label"><strong>Image:</strong> (Optional, leave blank to keep current)</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <small class="form-text text-muted">Max file size: 2MB. Allowed types: jpeg, png, jpg, gif, svg.</small>
                    @if($menuItem->image_path)
                        <div class="mt-2">
                            <p>Current Image:</p>
                            <img src="{{ asset('storage/' . $menuItem->image_path) }}" alt="{{ $menuItem->name }}" width="100" height="100" style="object-fit: cover;">
                        </div>
                    @else
                        <p class="mt-2">No current image.</p>
                    @endif
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_available" id="is_available" class="form-check-input" value="1" {{ old('is_available', $menuItem->is_available) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_available"><strong>Is Available?</strong></label>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('reception.menu-items.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Update Menu Item</button>
        </div>
    </form>
</div>
@endsection