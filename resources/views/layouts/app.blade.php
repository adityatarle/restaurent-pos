<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100"> {{-- Ensure html takes full height --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel Restaurant') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/darkly/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>


    <style>
        :root {
            --sidebar-bg: #0d1b2a;
            --sidebar-link: #b8c3cf;
            --sidebar-link-active: #ffffff;
            --sidebar-border: #1b263b;
        }
        body { display: flex; flex-direction: column; min-height: 100vh; }
        #app { display: flex; flex-direction: column; flex-grow: 1; }
        .main-wrapper { display: flex; flex-grow: 1; overflow: hidden; }
        #sidebar {
            flex-shrink: 0; width: 260px; overflow-y: auto; transition: width .2s ease-in-out;
            background: var(--sidebar-bg); border-right: 1px solid var(--sidebar-border);
        }
        #sidebar .nav-link { color: var(--sidebar-link); }
        #sidebar .nav-link.active, #sidebar .nav-link:hover { color: var(--sidebar-link-active); background-color: rgba(255,255,255,.06); }
        #sidebar .text-muted { color: #7e8a98 !important; }
        #sidebar.collapsed { width: 64px; }
        #sidebar .nav-link { display: flex; align-items: center; gap: .5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-radius: .4rem; }
        #sidebar.collapsed .nav-link { justify-content: center; }
        #sidebar.collapsed .nav-link .label { display: none; }
        .content-wrapper { flex-grow: 1; overflow-y: auto; background: #0b1321; }

        .sticky-in-content { position: sticky; top: 1rem; z-index: 1020; }
        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 2000; }
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
                <button id="sidebarToggle" class="btn btn-outline-light me-2" type="button"><i class="bi bi-list"></i></button>
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel Restaurant') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto"></ul>
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
            @auth
            <aside id="sidebar" class="bg-light border-end shadow-sm">
                <nav class="nav flex-column p-2">
                    {{-- Common Links --}}
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/"><i class="bi bi-house"></i><span class="label"> Home</span></a>

                    {{-- Waiter --}}
                    @if(Auth::user()->isWaiter() || Auth::user()->isSuperAdmin())
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Waiter</div>
                        <a class="nav-link {{ request()->routeIs('waiter.dashboard') ? 'active' : '' }}" href="{{ route('waiter.dashboard') }}"><i class="bi bi-grid"></i><span class="label"> Table View</span></a>
                    @endif

                    {{-- Reception --}}
                    @if(Auth::user()->isReception() || Auth::user()->isSuperAdmin())
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Reception</div>
                        <a class="nav-link {{ request()->routeIs('reception.dashboard') ? 'active' : '' }}" href="{{ route('reception.dashboard') }}"><i class="bi bi-speedometer2"></i><span class="label"> Dashboard</span></a>
                        <a class="nav-link {{ request()->routeIs('reception.categories.*') ? 'active' : '' }}" href="{{ route('reception.categories.index') }}"><i class="bi bi-tags"></i><span class="label"> Categories</span></a>
                        <a class="nav-link {{ request()->routeIs('reception.menu-items.*') ? 'active' : '' }}" href="{{ route('reception.menu-items.index') }}"><i class="bi bi-list-columns"></i><span class="label"> Menu Items</span></a>
                        <a class="nav-link {{ request()->routeIs('reception.notifications.*') ? 'active' : '' }}" href="{{ route('reception.notifications.index') }}">
                            <i class="bi bi-bell"></i><span class="label"> Notifications</span>
                            @php $unreadCount = Auth::user()->notifications()->where('is_read', false)->count(); @endphp
                            @if($unreadCount > 0) <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span> @endif
                        </a>
                    @endif

                    {{-- Super Admin --}}
                    @if(Auth::user()->isSuperAdmin())
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Admin</div>
                        <a class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}"><i class="bi bi-person-gear"></i><span class="label"> Admin Dashboard</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}" href="{{ route('superadmin.users.index') }}"><i class="bi bi-people"></i><span class="label"> Users</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.tables.*') ? 'active' : '' }}" href="{{ route('superadmin.tables.index') }}"><i class="bi bi-layout-text-sidebar"></i><span class="label"> Table Layout</span></a>
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Inventory</div>
                        <a class="nav-link {{ request()->routeIs('superadmin.inventory-items.*') ? 'active' : '' }}" href="{{ route('superadmin.inventory-items.index') }}"><i class="bi bi-boxes"></i><span class="label"> Items</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.suppliers.*') ? 'active' : '' }}" href="{{ route('superadmin.suppliers.index') }}"><i class="bi bi-truck"></i><span class="label"> Suppliers</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.inventory.valuation') ? 'active' : '' }}" href="{{ route('superadmin.reports.inventory.valuation') }}"><i class="bi bi-cash-coin"></i><span class="label"> Valuation</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.inventory.low_stock') ? 'active' : '' }}" href="{{ route('superadmin.reports.inventory.low_stock') }}"><i class="bi bi-exclamation-triangle"></i><span class="label"> Low Stock</span></a>
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Expenses</div>
                        <a class="nav-link {{ request()->routeIs('superadmin.expenses.*') ? 'active' : '' }}" href="{{ route('superadmin.expenses.index') }}"><i class="bi bi-wallet2"></i><span class="label"> Manage Expenses</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.expense-categories.*') ? 'active' : '' }}" href="{{ route('superadmin.expense-categories.index') }}"><i class="bi bi-ui-checks-grid"></i><span class="label"> Expense Categories</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.expenses') ? 'active' : '' }}" href="{{ route('superadmin.reports.expenses') }}"><i class="bi bi-graph-down"></i><span class="label"> Monthly Expenses</span></a>
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Sales</div>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.sales.summary') ? 'active' : '' }}" href="{{ route('superadmin.reports.sales.summary') }}"><i class="bi bi-activity"></i><span class="label"> Summary</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.sales.by_item') ? 'active' : '' }}" href="{{ route('superadmin.reports.sales.by_item') }}"><i class="bi bi-list-ul"></i><span class="label"> By Item</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.sales.by_category') ? 'active' : '' }}" href="{{ route('superadmin.reports.sales.by_category') }}"><i class="bi bi-grid-3x3-gap"></i><span class="label"> By Category</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.sales.by_waiter') ? 'active' : '' }}" href="{{ route('superadmin.reports.sales.by_waiter') }}"><i class="bi bi-person-check"></i><span class="label"> By Waiter</span></a>
                        <a class="nav-link {{ request()->routeIs('superadmin.reports.purchases.by_supplier') ? 'active' : '' }}" href="{{ route('superadmin.reports.purchases.by_supplier') }}"><i class="bi bi-bag-check"></i><span class="label"> Purchases by Supplier</span></a>
                    @endif
                </nav>
            </aside>
            @endauth

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
        // Sidebar toggle persistence
        (function(){
            const sidebar = document.getElementById('sidebar');
            const btn = document.getElementById('sidebarToggle');
            if (sidebar && btn) {
                const key = 'sidebarCollapsed';
                const saved = localStorage.getItem(key);
                if (saved === '1') sidebar.classList.add('collapsed');
                btn.addEventListener('click', () => {
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem(key, sidebar.classList.contains('collapsed') ? '1' : '0');
                });
            }
        })();

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