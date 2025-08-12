<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100"> {{-- Ensure html takes full height --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel Restaurant') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>


    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensures body takes at least full viewport height */
        }
        #app {
            display: flex;
            flex-direction: column;
            flex-grow: 1; /* Allows #app to grow and fill body height */
        }
        .main-wrapper {
            display: flex;
            flex-grow: 1; /* Allows this wrapper to fill remaining space in #app */
            overflow: hidden; /* Important: Prevents layout issues with children's overflow */
        }
        #sidebar {
            flex-shrink: 0; /* Prevents sidebar from shrinking if content is wide */
            width: 280px;   /* Or your preferred fixed width */
            overflow-y: auto; /* Allows sidebar content to scroll if it's too long */
            /* Consider adding bg-light, border-end directly or via Bootstrap classes */
        }
        .content-wrapper {
            flex-grow: 1; /* Allows content area to take up remaining horizontal space */
            overflow-y: auto; /* Allows main content to scroll independently */
            /* Padding (e.g., p-3 or p-4) will be added via Bootstrap class */
        }

        /* Helper for sticky elements within the .content-wrapper */
        .sticky-in-content {
            position: sticky;
            top: 1rem; /* Adjust based on .content-wrapper's padding */
            z-index: 1020; /* Default Bootstrap sticky-top z-index */
        }
    </style>
    @stack('styles')
<style>
.toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 2000; }
</style>
</head>
<body class="h-100"> {{-- Bootstrap class for 100% height --}}
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel Restaurant') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar (Content as before) -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(Auth::user()->isSuperAdmin())
                                <li class="nav-item"><a class="nav-link" href="{{ route('superadmin.dashboard') }}">Admin Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('superadmin.users.index') }}">Users</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('superadmin.tables.index') }}">Table Layout</a></li>
                                <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="inventoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Inventory
    </a>
    <ul class="dropdown-menu" aria-labelledby="inventoryDropdown">
        <li><a class="dropdown-item" href="{{ route('superadmin.inventory-items.index') }}">Inventory Items</a></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.suppliers.index') }}">Suppliers</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.inventory.valuation') }}">Inventory Valuation</a></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.inventory.low_stock') }}">Low Stock</a></li>
    </ul>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="expensesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Expenses
    </a>
    <ul class="dropdown-menu" aria-labelledby="expensesDropdown">
        <li><a class="dropdown-item" href="{{ route('superadmin.expenses.index') }}">Manage Expenses</a></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.expense-categories.index') }}">Expense Categories</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.expenses') }}">Monthly Expenses</a></li>
    </ul>
</li>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        Sales
    </a>
    <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.sales.summary') }}">Sales Summary</a></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.sales.by_item') }}">Sales by Item</a></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.sales.by_category') }}">Sales by Category</a></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.sales.by_waiter') }}">Sales by Waiter</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="{{ route('superadmin.reports.purchases.by_supplier') }}">Purchases by Supplier</a></li>
    </ul>
</li>
                            @endif
                            @if(Auth::user()->isReception() || Auth::user()->isSuperAdmin())
                                <li class="nav-item"><a class="nav-link" href="{{ route('reception.dashboard') }}">Reception Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('reception.categories.index') }}">Categories</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('reception.menu-items.index') }}">Menu Items</a></li>
                                 <li class="nav-item">
                                    <a class="nav-link" href="{{ route('reception.notifications.index') }}">
                                        Notifications
                                        @php $unreadCount = Auth::user()->notifications()->where('is_read', false)->count(); @endphp
                                        @if($unreadCount > 0) <span class="badge bg-danger">{{ $unreadCount }}</span> @endif
                                    </a>
                                </li>
                            @endif
                            @if(Auth::user()->isWaiter() || Auth::user()->isSuperAdmin())
                                <li class="nav-item"><a class="nav-link" href="{{ route('waiter.dashboard') }}">Waiter Dashboard</a></li>
                            @endif
                        @endauth
                    </ul>
                    <!-- Right Side Of Navbar (Content as before) -->
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login')) <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li> @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Logout') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="main-wrapper">
            @hasSection('sidebar')
                <aside id="sidebar" class="bg-light border-end shadow-sm">
                    {{-- The @yield('sidebar') content should ideally have its own padding --}}
                    @yield('sidebar')
                </aside>
            @endif

            <main class="content-wrapper p-4"> {{-- Added p-4 for padding around content --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5 class="alert-heading">Please correct the following errors:</h5>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Kitchen Print Modal (content as before) --}}
                @if (session('kitchen_print_content'))
                <div class="modal fade" id="kitchenPrintModal" tabindex="-1" aria-labelledby="kitchenPrintModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="kitchenPrintModalLabel">Kitchen Print Preview - Order #{{ session('kitchen_print_order_id') }} (Table: {{ session('kitchen_print_table_name') }})</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Waiter: {{ Auth::user()->name }}</strong></p>
                                <p><strong>Time: {{ now()->format('Y-m-d H:i:s') }}</strong></p>
                                <hr>
                                @foreach (session('kitchen_print_content') as $item)
                                    <div style="font-size: 1.2em; margin-bottom: 5px;">
                                        <strong>{{ $item['quantity'] }}x {{ $item['name'] }}</strong>
                                        @if(!empty($item['notes']))
                                            <br><small style="padding-left: 15px;"><em>- {{ $item['notes'] }}</em></small>
                                        @endif
                                    </div>
                                @endforeach
                                <hr>
                                <p class="text-center">--- END OF ORDER ---</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="window.print()">Print Actual Ticket</button>
                            </div>
                        </div>
                    </div>
                </div>
                @push('scripts')
                <script>
                    var kitchenPrintModal = new bootstrap.Modal(document.getElementById('kitchenPrintModal'));
                    kitchenPrintModal.show();
                </script>
                @endpush
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <div class="toast-container" id="toastContainer"></div>
    <script>
        // Optional: basic reconnection/backoff for socket.io global
        if (window.io) {
            const socket = io('http://localhost:3000', { withCredentials: true, reconnection: true, reconnectionDelay: 1000, reconnectionDelayMax: 5000 });
            window.appSocket = socket;
            @auth
            socket.on('connect', () => {
                socket.emit('authenticate', {
                    token: '{{ auth()->user()->createToken("socket")->plainTextToken }}',
                    role: '{{ auth()->user()->role }}',
                });
            });
            
            // Global notification toast
            socket.on('notification', (payload) => {
                const container = document.getElementById('toastContainer');
                const wrapper = document.createElement('div');
                wrapper.className = 'toast align-items-center text-bg-info border-0 show mb-2';
                wrapper.setAttribute('role', 'alert');
                wrapper.setAttribute('aria-live', 'assertive');
                wrapper.setAttribute('aria-atomic', 'true');
                wrapper.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            ${payload.link ? `<a class=\"text-white\" href=\"${payload.link}\">${payload.message}</a>` : payload.message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>`;
                container.appendChild(wrapper);
                // Auto hide after 5s
                setTimeout(() => wrapper.remove(), 5000);
            });
            @endauth
        }
    </script>
    @stack('scripts')
</body>
</html>