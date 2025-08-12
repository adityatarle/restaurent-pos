@extends('layouts.app') {{-- Assuming you have a layouts.app for consistent structure --}}

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('All Notifications for Reception') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($notifications->count() > 0)
                        <ul class="list-group">
                            @foreach($notifications as $notification)
                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $notification->is_read ? 'list-group-item-light text-muted' : 'list-group-item-warning' }}">
                                    <div>
                                        <small>{{ $notification->created_at->format('M d, Y H:i A') }} ({{ $notification->created_at->diffForHumans() }})</small><br>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $notification->type)) }}:</strong>
                                        <p class="mb-0">{{ $notification->message }}</p>
                                        @if($notification->link)
                                            <a href="{{ Str::startsWith($notification->link, 'http') ? $notification->link : url($notification->link) }}" class="btn btn-sm btn-outline-info mt-1">View Details</a>
                                        @endif
                                    </div>
                                    <div>
                                        @if(!$notification->is_read)
                                            <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Read">
                                                    <i class="bi bi-check-lg"></i> Mark Read
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-secondary">Read</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-3">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <p class="text-center">You have no notifications.</p>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('reception.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection