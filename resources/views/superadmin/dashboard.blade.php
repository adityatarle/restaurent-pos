{{-- resources/views/superadmin/dashboard.blade.php --}}

@extends('layouts.app') {{-- Assuming you have a layouts.app for consistent structure --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Super Admin Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h1>Welcome, Super Admin!</h1>
                    <p>This is your main dashboard. From here you can manage users, settings, and view reports.</p>

                    {{-- Add links or cards to other super admin sections --}}
                    <div class="list-group mt-3">
                        <a href="{{ route('superadmin.users.index') }}" class="list-group-item list-group-item-action">Manage Users</a>
                        <a href="{{ route('superadmin.tables.index') }}" class="list-group-item list-group-item-action">Manage Table Layout</a>
                        <a href="{{ route('superadmin.settings.index') }}" class="list-group-item list-group-item-action">Restaurant Settings</a>
                        {{-- Add more links as needed --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection