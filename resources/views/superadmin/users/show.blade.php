@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Details: {{ $user->name }}</h1>
        <div>
            <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-warning">Edit User</a>
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">Back to Users List</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            User Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>ID:</strong> {{ $user->id }}</p>
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Role:</strong> <span class="text-capitalize">{{ $user->role }}</span></p>
                    <p><strong>Email Verified At:</strong> {{ $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'Not Verified' }}</p>
                    <p><strong>Joined On:</strong> {{ $user->created_at->format('Y-m-d H:i:s') }}</p>
                    <p><strong>Last Updated:</strong> {{ $user->updated_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- You could add more details here, like activity logs or related data if applicable --}}

</div>
@endsection