<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel Restaurant') }}</title>

    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <style>
        :root {
            --sidebar-width: 240px;
            --sidebar-collapsed-width: 100px;
            --sidebar-bg: #0b1320;          /* Dark navy background */
            --sidebar-border: #1a2742;
            --sidebar-link: #e6edf3;        /* Light text */
            --sidebar-link-active: #ffffff; /* Active/hover text */
            --sidebar-hover: #24324d;       /* Hover background */
        }

        .bg-dark {
            --bs-bg-opacity: 1;
            background-color: rgb(11 19 32) !important;
        }

        body { display: flex; flex-direction: column; min-height: 100vh; }
        #app { display: flex; flex-direction: column; flex-grow: 1; }
        .main-wrapper { display: flex; flex-grow: 1; overflow: hidden; }

        /* Sidebar */
        #sidebar {
            flex-shrink: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            color: #fff;
            overflow-y: auto;
            transition: width .3s ease;
        }
        #sidebar.collapsed { 
            width: var(--sidebar-collapsed-width);
            text-align: center;
         }
        #sidebar .nav-link {
            display: flex; align-items: center; gap: .5rem;
            color: var(--sidebar-link);
            border-radius: .4rem;
            padding: .55rem .75rem;
            white-space: nowrap;
            margin: 2px;
        }
        #sidebar .nav-link .bi { min-width: 1.25rem; text-align: center; }
        #sidebar .nav-link.active,
        #sidebar .nav-link:hover {
            color: var(--sidebar-link-active);
            background: var(--sidebar-hover);
        }
        #sidebar .text-muted {
            color: #a7b6cb !important;
            letter-spacing: .02em;
            font-weight: 600;
        }
        #sidebar.collapsed .nav-link { justify-content: center; }
        #sidebar.collapsed .nav-link .label { display: none; }

        /* Content */
        .content-wrapper {
            flex-grow: 1;
            overflow-y: auto;
            background: #f7fafc;
            padding: 1.5rem;
            transition: margin-left .3s ease;
        }

        



        /* Mobile: sidebar stays collapsed with only icons */
        @media (max-width: 992px) {
            #sidebar { 
                {{-- position: fixed; --}}
                top: 0; bottom: 0; left: 0;
                width: var(--sidebar-collapsed-width) !important; /* collapsed width */
                z-index: 1050;
            }

            /* force collapsed styles on mobile */
            #sidebar .label { display: none; }
            #sidebar .nav-link { justify-content: center; }
        }


        .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 2000; }
    </style>
    @stack('styles')
</head>
<body class="h-100">
    <div id="app">
        {{-- Navbar --}}
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <button id="sidebarToggle" class="btn btn-outline-light me-2" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel Restaurant') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto"></ul>
                    <ul class="navbar-nav ms-auto">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                       {{ __('Logout') }}
                                    </a>
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
            {{-- Sidebar --}}
            <aside id="sidebar">
                <nav class="nav flex-column">
                    {{-- Common Links --}}
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">
                        <i class="bi bi-house"></i><span class="label"> Home</span>
                    </a>

                    {{-- Waiter --}}
                    @if(Auth::user()->isWaiter() || Auth::user()->isSuperAdmin())
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Waiter</div>
                        <a class="nav-link {{ request()->routeIs('waiter.dashboard') ? 'active' : '' }}"
                           href="{{ route('waiter.dashboard') }}">
                            <i class="bi bi-grid"></i><span class="label"> Table View</span>
                        </a>
                    @endif

                    {{-- Reception --}}
                    @if(Auth::user()->isReception() || Auth::user()->isSuperAdmin())
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Reception</div>
                        <a class="nav-link {{ request()->routeIs('reception.dashboard') ? 'active' : '' }}"
                           href="{{ route('reception.dashboard') }}">
                            <i class="bi bi-speedometer2"></i><span class="label"> Dashboard</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('reception.categories.*') ? 'active' : '' }}"
                           href="{{ route('reception.categories.index') }}">
                            <i class="bi bi-tags"></i><span class="label"> Categories</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('reception.menu-items.*') ? 'active' : '' }}"
                           href="{{ route('reception.menu-items.index') }}">
                            <i class="bi bi-list-columns"></i><span class="label"> Menu Items</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('reception.notifications.*') ? 'active' : '' }}"
                           href="{{ route('reception.notifications.index') }}">
                            <i class="bi bi-bell"></i><span class="label"> Notifications</span>
                            @php $unreadCount = Auth::user()->notifications()->where('is_read', false)->count(); @endphp
                            @if($unreadCount > 0) <span class="badge bg-danger">{{ $unreadCount }}</span> @endif
                        </a>
                    @endif

                    {{-- Super Admin --}}
                    @if(Auth::user()->isSuperAdmin())
                        <div class="text-uppercase text-muted small mt-3 mb-1 px-2">Admin</div>
                        <a class="nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}"
                           href="{{ route('superadmin.dashboard') }}">
                            <i class="bi bi-person-gear"></i><span class="label"> Admin Dashboard</span>
                        </a>
                        <a class="nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}"
                           href="{{ route('superadmin.users.index') }}">
                            <i class="bi bi-people"></i><span class="label"> Users</span>
                        </a>
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
                        {{-- Add your other superadmin links here --}}
                    @endif
                </nav>
            </aside>
            @endauth

            {{-- Content --}}
            <main class="content-wrapper">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h5 class="alert-heading">Please correct the following errors:</h5>
                        <ul>
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <div class="toast-container" id="toastContainer"></div>
    <script>
       const sidebar = document.getElementById('sidebar');
        const btn = document.getElementById('sidebarToggle');

        if (sidebar && btn) {
            const key = 'sidebarCollapsed';
            const saved = localStorage.getItem(key);

            // Only apply collapse/expand for desktop
            if (window.innerWidth >= 992 && saved === '1') {
                sidebar.classList.add('collapsed');
            }

            btn.addEventListener('click', () => {
                if (window.innerWidth >= 992) {
                    // Desktop → toggle collapse
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem(key, sidebar.classList.contains('collapsed') ? '1' : '0');
                } 
                // On mobile, do nothing (always collapsed with icons only)
            });
        }

    </script>
    @stack('scripts')
</body>
</html>
