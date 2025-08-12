@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Restaurant Settings</h1>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
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

    <form action="{{ route('superadmin.settings.update') }}" method="POST">
        @csrf
        {{-- @method('PUT') or @method('PATCH') if you prefer, but POST is also fine for this route --}}

        <div class="card">
            <div class="card-header">General Settings</div>
            <div class="card-body">
                <div class="mb-3 row">
                    <label for="restaurant_name" class="col-sm-3 col-form-label">Restaurant Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="restaurant_name" name="restaurant_name" value="{{ old('restaurant_name', $settings['restaurant_name'] ?? '') }}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="default_currency" class="col-sm-3 col-form-label">Default Currency (e.g., USD, EUR)</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="default_currency" name="default_currency" value="{{ old('default_currency', $settings['default_currency'] ?? 'USD') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Printer Settings</div>
            <div class="card-body">
                <div class="mb-3 row">
                    <label for="kitchen_printer_ip" class="col-sm-3 col-form-label">Kitchen Printer IP Address</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="kitchen_printer_ip" name="kitchen_printer_ip" placeholder="e.g., 192.168.1.100" value="{{ old('kitchen_printer_ip', $settings['kitchen_printer_ip'] ?? '') }}">
                        <small class="form-text text-muted">Leave blank if not applicable.</small>
                    </div>
                </div>
                {{-- Add more printer settings as needed --}}
            </div>
        </div>

         <div class="card mt-3">
            <div class="card-header">Financial Settings</div>
            <div class="card-body">
                <div class="mb-3 row">
                    <label for="service_charge_percentage" class="col-sm-3 col-form-label">Service Charge (%)</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="service_charge_percentage" name="service_charge_percentage" value="{{ old('service_charge_percentage', $settings['service_charge_percentage'] ?? '0') }}">
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="tax_rate_percentage" class="col-sm-3 col-form-label">Tax Rate (%)</label>
                    <div class="col-sm-9">
                        <input type="number" step="0.01" class="form-control" id="tax_rate_percentage" name="tax_rate_percentage" value="{{ old('tax_rate_percentage', $settings['tax_rate_percentage'] ?? '0') }}">
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection