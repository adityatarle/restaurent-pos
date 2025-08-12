@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Waiter Dashboard - Table View</h1>
    <div class="row" id="waiter-tables">
        @include('waiter.partials.table_grid', ['tables' => $tables])
    </div>
</div>
@endsection

@push('styles')
<style>
    .table-card.status-available .card-header { background-color: #d1e7dd; border-color: #badbcc; }
    .table-card.status-occupied .card-header { background-color: #f8d7da; border-color: #f5c2c7; }
    .table-card.status-reserved .card-header { background-color: #fff3cd; border-color: #ffecb5; }
    .table-card.my-table { border: 2px solid blue; }
</style>
@endpush

@push('scripts')
<script>
    const waiterTablesEl = document.getElementById('waiter-tables');

    function refreshTables() {
        fetch('{{ route('waiter.tables.partial') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(r => r.text())
            .then(html => {
                waiterTablesEl.innerHTML = html;
            }).catch(() => {});
    }

    if (window.io) {
        const socket = io('http://localhost:3000', { withCredentials: true });
        socket.on('connect', () => {
            socket.emit('authenticate', { token: '{{ auth()->user()->createToken("socket")->plainTextToken }}', role: '{{ auth()->user()->role }}' });
        });
        socket.on('table_updated', refreshTables);
        socket.on('order_updated', refreshTables);
    }
</script>
@endpush