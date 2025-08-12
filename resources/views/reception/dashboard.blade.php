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
    const socket = io('http://localhost:3000', { withCredentials: true });

    socket.on('connect', () => {
        socket.emit('authenticate', {
            token: '{{ auth()->user()->createToken("socket")->plainTextToken }}',
            role: '{{ auth()->user()->role }}',
        });
    });

    socket.on('authenticated', (data) => {
        if (!data.success) {
            console.error('Authentication failed:', data.error);
        }
    });

    async function loadData() {
        const [tablesResponse, ordersResponse] = await Promise.all([
            fetch('/api/reception/tables', {
                headers: { 'Authorization': 'Bearer {{ auth()->user()->createToken("api")->plainTextToken }}' },
            }),
            fetch('/api/reception/orders', {
                headers: { 'Authorization': 'Bearer {{ auth()->user()->createToken("api")->plainTextToken }}' },
            }),
        ]);
        const tables = await tablesResponse.json();
        const orders = await ordersResponse.json();

        renderTables(tables);
        renderOrders(orders);
    }

    function renderTables(tables) {
        const tablesDiv = document.getElementById('tables');
        tablesDiv.innerHTML = tables.map(table => `
            <div class="col-md-4 mb-3">
                <div class="card ${table.status === 'occupied' ? 'border-warning' : 'border-success'}">
                    <div class="card-body">
                        <h5>Table ${table.name}</h5>
                        <p>Status: <span class="badge ${table.status === 'occupied' ? 'bg-warning' : 'bg-success'}">${table.status}</span></p>
                        ${table.current_order ? `
                            <p>Order #${table.current_order.id}</p>
                            <p>Waiter: ${table.current_order.waiter_name}</p>
                            <p>Customers: ${table.current_order.customer_count}</p>
                            <p>Status: ${table.current_order.status}</p>
                        ` : '<p>No active order</p>'}
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
        fetch('/api/reception/tables', {
            headers: { 'Authorization': 'Bearer {{ auth()->user()->createToken("api")->plainTextToken }}' },
        })
            .then(res => res.json())
            .then(tables => renderTables(tables));
    });

    socket.on('order_updated', () => {
        fetch('/api/reception/orders', {
            headers: { 'Authorization': 'Bearer {{ auth()->user()->createToken("api")->plainTextToken }}' },
        })
            .then(res => res.json())
            .then(orders => renderOrders(orders));
    });

    loadData();
</script>
@endpush