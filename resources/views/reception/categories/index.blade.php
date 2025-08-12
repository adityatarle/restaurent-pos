@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Menu Categories</h1>
    <div class="mb-3">
        <a href="{{ route('reception.categories.create') }}" class="btn btn-primary">Add New Category</a>
    </div>

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
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th width="280px">Action</th>
        </tr>
        @forelse ($categories as $i => $category)
        <tr>
            <td>{{ $i + 1 + (($categories->currentPage() - 1) * $categories->perPage()) }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->description ?: '-' }}</td>
            <td>
                <form action="{{ route('reception.categories.destroy',$category->id) }}" method="POST">
                    <a class="btn btn-info btn-sm" href="{{ route('reception.categories.show',$category->id) }}">Show</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('reception.categories.edit',$category->id) }}">Edit</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">No categories found.</td>
        </tr>
        @endforelse
    </table>
    {!! $categories->links() !!}
</div>
@endsection