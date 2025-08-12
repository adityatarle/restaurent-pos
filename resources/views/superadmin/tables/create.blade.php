@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add New Restaurant Table</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('superadmin.tables.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Table Name/Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', 2) }}" min="1" required>
                    @error('capacity')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Initial Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied (Not Recommended for initial)</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Optional: For visual layout --}}
                {{-- <div class="mb-3">
                    <label for="visual_coordinates" class="form-label">Visual Coordinates (JSON format, e.g., {"x": 10, "y": 20})</label>
                    <input type="text" class="form-control @error('visual_coordinates') is-invalid @enderror" id="visual_coordinates" name="visual_coordinates" value="{{ old('visual_coordinates') }}" placeholder='{"x": 10, "y": 20}'>
                    @error('visual_coordinates')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> --}}

                <button type="submit" class="btn btn-primary">Save Table</button>
                <a href="{{ route('superadmin.tables.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection