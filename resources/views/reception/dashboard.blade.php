@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Reception Dashboard</h2>
    <div class="row">
        <div class="col-md-6">
            <h3>Tables</h3>
            <div id="tables" class="row">
                <!-- Tables rendered here -->
            </div>
        </div>
        <div class="col-md-6">
            <h3>Ongoing Orders</h3>
            <div id="orders">
                <!-- Orders rendered here -->
            </div>
        </div>
    </div>
    <div class="mt-4">
        <h3>Notifications</h3>
        <div id="notifications">
            @foreach($notifications as $notification)
                <div class="alert alert-info">{{ $notification->message }}</div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const socket = window.appSocket || io('http://localhost:3000', { withCredentials: true });
    socket.on('authenticated', (data) => {
        if (!data.success) {
            console.error('Authentication failed:', data.error);
        }
    });

    async function loadData() {
        const [tablesResponse, ordersResponse] = await Promise.all([
            fetch('/reception/tables'),
            fetch('/reception/orders'),
        ]);
        const tables = await tablesResponse.json();
        const orders = await ordersResponse.json();

        renderTables(tables);
        renderOrders(orders);
    }

    function renderTables(tables) {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const tablesDiv = document.getElementById('tables');
        tablesDiv.innerHTML = tables.map(table => `
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 ${table.status === 'occupied' ? 'bg-light' : 'bg-white'}">
                    <div class="card-body">
                        <h5>Table ${table.name}</h5>
                        <p>Status: <span class="badge ${table.status === 'occupied' ? 'bg-warning' : 'bg-success'}">${table.status}</span></p>
                        ${table.current_order ? `
                            <p>Order #${table.current_order.id}</p>
                            <p>Waiter: ${table.current_order.waiter_name}</p>
                            <p>Customers: ${table.current_order.customer_count}</p>
                            <p>Status: ${table.current_order.status}</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a class="btn btn-sm btn-outline-primary" href="/reception/orders/${table.current_order.id}/bill" target="_blank">Bill</a>
                                <button class="btn btn-sm btn-success" onclick="(async()=>{await fetch('/reception/orders/${table.current_order.id}/pay',{method:'POST',headers:{'X-CSRF-TOKEN':'${csrf}'} });})()">Mark Paid</button>
                                <button class="btn btn-sm btn-outline-danger" onclick="(async()=>{await fetch('/reception/tables/${table.id}/vacate',{method:'POST',headers:{'X-CSRF-TOKEN':'${csrf}'}});})()">Vacate</button>
                            </div>
                        ` : `
                            <p>No active order</p>
                            <div>
                                <button class="btn btn-sm btn-outline-secondary" disabled>Available</button>
                            </div>
                        `}
                    </div>
                </div>
            </div>
        `).join('');
    }

    function renderOrders(orders) {
        const ordersDiv = document.getElementById('orders');
        ordersDiv.innerHTML = orders.length ? `
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Table</th>
                        <th>Waiter</th>
                        <th>Customers</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    ${orders.map(order => `
                        <tr>
                            <td>${order.id}</td>
                            <td>${order.table_name}</td>
                            <td>${order.waiter_name}</td>
                            <td>${order.customer_count}</td>
                            <td>${order.status}</td>
                            <td>$${order.total_amount}</td>
                            <td>${order.updated_at}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        ` : '<p>No ongoing orders</p>';
    }

    socket.on('table_updated', () => {
        fetch('/reception/tables')
            .then(res => res.json())
            .then(tables => renderTables(tables));
    });

    socket.on('order_updated', (payload) => {
        // If we receive full order payload, update orders list for that order only; else refetch
        if (payload && payload.order) {
            // naive refetch for simplicity to keep code small
            fetch('/reception/orders')
                .then(res => res.json())
                .then(orders => renderOrders(orders));
        } else {
            fetch('/reception/orders')
                .then(res => res.json())
                .then(orders => renderOrders(orders));
        }
    });

    socket.on('notification', (payload) => {
        const box = document.getElementById('notifications');
        const alert = document.createElement('div');
        alert.className = 'alert alert-info';
        alert.innerHTML = payload.link ? `<a href="${payload.link}">${payload.message}</a>` : payload.message;
        box.prepend(alert);
        // Also refresh tables/orders quickly so the dashboard reflects changes instantly
        fetch('/reception/tables').then(r=>r.json()).then(renderTables);
        fetch('/reception/orders').then(r=>r.json()).then(renderOrders);
    });

    loadData();
</script>
@endpush