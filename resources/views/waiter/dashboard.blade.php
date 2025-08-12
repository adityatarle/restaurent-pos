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
    .table-card.status-available .card-header { background: #123524; color: #d1fae5; }
    .table-card.status-occupied .card-header { background: #3a2a00; color: #fde68a; }
    .table-card.status-reserved .card-header { background: #0b2545; color: #cfe8ff; }
    .table-card.my-table { border: 2px solid #00d1b2; }
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