@extends('layouts.app')

@php
    // Determine selected category ID. Default to the first category if none is selected.
    $selectedCategoryId = request('category_id', $categories->isNotEmpty() ? $categories->first()->id : null);
    $currentCategory = null; // Initialize
    $selectedMenuItems = collect(); // Initialize as empty collection
    if ($selectedCategoryId) {
        $currentCategory = $categories->firstWhere('id', $selectedCategoryId);
        if ($currentCategory) {
            $selectedMenuItems = $currentCategory->menuItems;
        }
    }
@endphp



@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Left Side: Menu Item Selection --}}
        <div class="col-lg-7 col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Menu Items {{ $selectedCategoryId && $currentCategory ? '('.$currentCategory->name.')' : '' }}</h3>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-info fs-6">Order #{{ $order->id }} - Table {{ $order->restaurantTable->name }}</span>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Category</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @forelse($categories as $category)
                            <li><a class="dropdown-item {{ $selectedCategoryId == $category->id ? 'active' : '' }}" href="{{ route('waiter.orders.show', ['order' => $order->id, 'category_id' => $category->id]) }}">{{ $category->name }}</a></li>
                            @empty
                            <li><span class="dropdown-item text-muted">No categories</span></li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            @if($order->status === 'paid' || $order->status === 'cancelled')
                <div class="alert {{ $order->status === 'paid' ? 'alert-success' : 'alert-danger' }}" role="alert">
                    This order is <strong>{{ $order->status }}</strong> and cannot be modified.
                </div>
            @endif

            @if($selectedMenuItems->count() > 0 && $order->status !== 'paid' && $order->status !== 'cancelled')
                <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3"> {{-- Adjusted row-cols for better responsiveness --}}
                    @foreach($selectedMenuItems as $item)
                    <div class="col">
                        <div class="card h-100 menu-item-card shadow-sm">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <h6 class="card-title flex-grow-1">{{ $item->name }}</h6>
                                <p class="card-text text-muted small mb-2">${{ number_format($item->price, 2) }}</p>
                                <form action="{{ route('waiter.orders.add-item', $order->id) }}" method="POST" class="mt-auto">
                                    @csrf
                                    <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-plus-circle"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @elseif($order->status !== 'paid' && $order->status !== 'cancelled')
                @if($categories->isEmpty())
                <p class="text-muted">No categories or menu items have been set up yet.</p>
                @elseif(!$selectedCategoryId)
                <p class="text-muted">Please select a category from the sidebar to view items.</p>
                @else
                <p class="text-muted">No items in {{ $currentCategory ? $currentCategory->name : 'this category'}}, or category not found.</p>
                @endif
            @endif
        </div>

        {{-- Right Side: Order Summary and Actions --}}
        <div class="col-lg-5 col-md-6">
            {{-- Use `sticky-in-content` if Bootstrap's `sticky-top` doesn't work as expected
                 due to the scrolling parent (.content-wrapper) --}}
            <div class="card sticky-in-content shadow">
                <div class="card-header">
                    <h5 class="mb-0">Current Order ({{ $order->customer_count }} <i class="bi bi-people-fill"></i>)</h5>
                    Status: <span class="text-capitalize fw-bold">{{ $order->status }}</span>
                </div>
                {{-- Adjust max-height for scrollable order items. calc() can be complex.
                     A fixed vh or a value considering navbar height and padding might be better.
                     Example: style="max-height: calc(100vh - 230px); overflow-y: auto;" (230px is an estimate)
                     Or simply style="max-height: 60vh; overflow-y: auto;"
                --}}
                <div class="card-body" style="max-height: 65vh; overflow-y: auto;">
                    @if($order->orderItems->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($order->orderItems->sortByDesc('created_at') as $orderItem)
                        <li class="list-group-item {{ $orderItem->status == 'cancelled' ? 'list-group-item-danger opacity-75' : ($orderItem->printed_to_kitchen ? 'list-group-item-success opacity-90' : '') }}">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $orderItem->menuItem->name }}</h6>
                                <small class="text-muted">${{ number_format($orderItem->price_at_order * $orderItem->quantity, 2) }}</small>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <div class="quantity-controls">
                                    @if($order->status !== 'paid' && $order->status !== 'cancelled' && $orderItem->status !== 'cancelled')
                                    <form action="{{ route('waiter.orders.update-item', [$order->id, $orderItem->id]) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $orderItem->quantity - 1 }}">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" {{ $orderItem->quantity <= 1 ? 'disabled' : '' }} title="Decrease quantity"><i class="bi bi-dash"></i></button>
                                    </form>
                                    <span class="mx-1 fw-bold">{{ $orderItem->quantity }}</span>
                                    <form action="{{ route('waiter.orders.update-item', [$order->id, $orderItem->id]) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $orderItem->quantity + 1 }}">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Increase quantity"><i class="bi bi-plus"></i></button>
                                    </form>
                                    @else
                                    <span class="mx-1">Qty: {{ $orderItem->quantity }}</span>
                                    @endif
                                </div>
                                <div>
                                    @if($orderItem->printed_to_kitchen && $orderItem->status != 'cancelled')
                                        <span class="badge bg-info-subtle text-info-emphasis me-1">Sent</span>
                                    @endif
                                    @if($orderItem->status == 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @else
                                        @if($order->status !== 'paid' && $order->status !== 'cancelled')
                                        <form action="{{ route('waiter.orders.remove-item', [$order->id, $orderItem->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this item? It will notify kitchen if already printed.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-1" title="Cancel Item"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            @if($orderItem->item_notes)
                            <small class="d-block text-muted fst-italic mt-1">Notes: {{ $orderItem->item_notes }}</small>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-center text-muted mt-3">No items in this order yet. Select from the menu.</p>
                    @endif
                </div>
                <div class="card-footer">
                    <h5 class="text-end">Total: ${{ number_format($order->total_amount, 2) }}</h5>
                     @if($order->status !== 'paid' && $order->status !== 'cancelled')
                        <form action="{{ route('waiter.orders.print-kitchen', $order->id) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 mb-2" {{ $order->getUnprintedItemsCount() > 0 ? '' : 'disabled' }}>
                                <i class="bi bi-printer-fill"></i> Save & Print New Items ({{ $order->getUnprintedItemsCount() }})
                            </button>
                        </form>
                        <form action="{{ route('waiter.orders.print-kitchen', $order->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="force_reprint_all" value="1">
                            <button type="submit" class="btn btn-info w-100 mb-2" {{ $order->orderItems()->where('status', '!=', 'cancelled')->count() > 0 ? '' : 'disabled' }}>
                                <i class="bi bi-printer"></i> Reprint Entire Order
                            </button>
                        </form>
                    @endif
                </div>
            </div>
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

    function renderOrderSummary(order) {
        const orderItemsList = document.querySelector('.list-group-flush');
        const totalDisplay = document.querySelector('.card-footer h5');
        const printButton = document.querySelector('.btn-success');
        const reprintButton = document.querySelector('.btn-info');

        orderItemsList.innerHTML = order.order_items
            .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            .map(item => `
                <li class="list-group-item ${item.status === 'cancelled' ? 'list-group-item-danger opacity-75' : item.printed_to_kitchen ? 'list-group-item-success opacity-90' : ''}">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${item.menu_item_name}</h6>
                        <small class="text-muted">$${item.quantity * item.price_at_order}</small>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <div class="quantity-controls">
                            ${order.status !== 'paid' && order.status !== 'cancelled' && item.status !== 'cancelled' ? `
                                <form action="/waiter/orders/${order.id}/items/${item.id}" method="POST" class="d-inline">
                                    <input type="hidden" name="_method" value="PATCH">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                    <input type="hidden" name="quantity" value="${item.quantity - 1}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" ${item.quantity <= 1 ? 'disabled' : ''}><i class="bi bi-dash"></i></button>
                                </form>
                                <span class="mx-1 fw-bold">${item.quantity}</span>
                                <form action="/waiter/orders/${order.id}/items/${item.id}" method="POST" class="d-inline">
                                    <input type="hidden" name="_method" value="PATCH">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                    <input type="hidden" name="quantity" value="${item.quantity + 1}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1"><i class="bi bi-plus"></i></button>
                                </form>
                            ` : `<span class="mx-1">Qty: ${item.quantity}</span>`}
                        </div>
                        <div>
                            ${item.printed_to_kitchen && item.status !== 'cancelled' ? '<span class="badge bg-info-subtle text-info-emphasis me-1">Sent</span>' : ''}
                            ${item.status === 'cancelled' ? '<span class="badge bg-danger">Cancelled</span>' : order.status !== 'paid' && order.status !== 'cancelled' ? `
                                <form action="/waiter/orders/${order.id}/items/${item.id}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this item?');">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                    <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-1"><i class="bi bi-x-lg"></i></button>
                                </form>
                            ` : ''}
                        </div>
                    </div>
                    ${item.item_notes ? `<small class="d-block text-muted fst-italic mt-1">Notes: ${item.item_notes}</small>` : ''}
                </li>
            `).join('');

        totalDisplay.textContent = `Total: $${order.total_amount}`;

        const unprintedCount = order.order_items.filter(item => !item.printed_to_kitchen && item.status !== 'cancelled').length;
        printButton.disabled = unprintedCount === 0;
        printButton.innerHTML = `<i class="bi bi-printer-fill"></i> Save & Print New Items (${unprintedCount})`;
        reprintButton.disabled = order.order_items.filter(item => item.status !== 'cancelled').length === 0;
    }

    socket.on('order_updated', (data) => {
        if (data.order.id === {{ $order->id }}) {
            renderOrderSummary(data.order);
        }
    });
</script>
@endpush

@push('styles')
<style>
    .menu-item-card {
        cursor: pointer;
        transition: transform 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
    }
    .menu-item-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15) !important; /* Ensure hover shadow is prominent */
    }
    .menu-item-card .card-body {
        padding: 0.75rem;
    }
    .menu-item-card .card-title {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        min-height: 2.7em; /* Approx 2-3 lines */
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    /* Using Bootstrap's contextual classes for list items is often cleaner than custom overrides */
</style>
@endpush