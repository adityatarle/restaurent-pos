@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Restaurant Tables</h1>
    <div class="mb-3">
        <a href="{{ route('superadmin.tables.create') }}" class="btn btn-primary">Add New Table</a>
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
            <th>Capacity</th>
            <th>Status</th>
            <th>Coordinates (Optional)</th>
            <th width="280px">Action</th>
        </tr>
        @forelse ($tables as $i => $table)
        <tr>
            <td>{{ $i + 1 + (($tables->currentPage() - 1) * $tables->perPage()) }}</td>
            <td>{{ $table->name }}</td>
            <td>{{ $table->capacity }}</td>
            <td class="text-capitalize">{{ $table->status }}</td>
            <td>{{ $table->visual_coordinates ?: '-' }}</td>
            <td>
                <form action="{{ route('superadmin.tables.destroy',$table->id) }}" method="POST">
                    <a class="btn btn-info btn-sm" href="{{ route('superadmin.tables.show',$table->id) }}">Show</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('superadmin.tables.edit',$table->id) }}">Edit</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this table?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">No tables found.</td>
        </tr>
        @endforelse
    </table>
    {!! $tables->links() !!}
</div>
@endsection