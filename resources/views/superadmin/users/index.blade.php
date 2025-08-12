@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Manage Users</h1>
    <div class="mb-3">
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">Add New User</a>
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
            <th>Email</th>
            <th>Role</th>
            <th width="280px">Action</th>
        </tr>
        @forelse ($users as $i => $user)
        <tr>
            <td>{{ $i + 1 + (($users->currentPage() - 1) * $users->perPage()) }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td class="text-capitalize">{{ $user->role }}</td>
            <td>
                <form action="{{ route('superadmin.users.destroy',$user->id) }}" method="POST">
                    <a class="btn btn-info btn-sm" href="{{ route('superadmin.users.show',$user->id) }}">Show</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('superadmin.users.edit',$user->id) }}">Edit</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">No users found.</td>
        </tr>
        @endforelse
    </table>
    {!! $users->links() !!}
</div>
@endsection