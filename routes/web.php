<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\RestaurantTableController as AdminTableController;
use App\Http\Controllers\SuperAdmin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Reception\MenuCategoryController;
use App\Http\Controllers\Reception\MenuItemController;
use App\Http\Controllers\Reception\BillingController;
use App\Http\Controllers\Reception\ReceptionDashboardController;
use App\Http\Controllers\Waiter\WaiterDashboardController;
use App\Http\Controllers\Waiter\OrderController as WaiterOrderController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\SuperAdmin\InventoryItemController;
use App\Http\Controllers\SuperAdmin\SupplierController;
use App\Http\Controllers\SuperAdmin\ExpenseCategoryController;
use App\Http\Controllers\SuperAdmin\ExpenseController;
use App\Http\Controllers\SuperAdmin\StockTransactionController; // For stock adjustments
use App\Http\Controllers\SuperAdmin\ReportController; // For reports


Auth::routes(); // If you installed laravel/ui

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [HomeController::class, 'dashboardRedirect'])->name('dashboard.redirect')->middleware('auth');


// Super Admin Routes
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('dashboard', [HomeController::class, 'superAdminDashboard'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('tables', AdminTableController::class); // Table layout config
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    // More reports, analytics routes here
});

// Reception Routes
Route::middleware(['auth', 'reception'])->prefix('reception')->name('reception.')->group(function () {
    Route::get('dashboard', [ReceptionDashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', MenuCategoryController::class);
    Route::resource('menu-items', MenuItemController::class);
    Route::get('orders/{order}/bill', [BillingController::class, 'generateBill'])->name('bill.generate');
    Route::post('orders/{order}/bill/update', [BillingController::class, 'updateTotals'])->name('bill.update');
    Route::post('orders/{order}/pay', [BillingController::class, 'markAsPaid'])->name('bill.pay');
    Route::post('tables/{table}/vacate', [\App\Http\Controllers\Reception\TableController::class, 'vacate'])->name('tables.vacate');
    Route::get('notifications', [NotificationController::class, 'indexForReception'])->name('notifications.index');
});

// Waiter Routes
Route::middleware(['auth', 'waiter'])->prefix('waiter')->name('waiter.')->group(function () {
    Route::get('dashboard', [WaiterDashboardController::class, 'index'])->name('dashboard'); // Table view
    Route::get('tables/partial', [WaiterDashboardController::class, 'tablesPartial'])->name('tables.partial');

    // Table Assignment
    Route::post('tables/{table}/assign', [WaiterDashboardController::class, 'assignTable'])->name('tables.assign');
    Route::post('tables/{table}/unassign', [WaiterDashboardController::class, 'unassignTable'])->name('tables.unassign'); // If needed
    Route::post('tables/{table}/request-bill', [WaiterDashboardController::class, 'requestBill'])->name('tables.request_bill');

    // Order Management
    Route::get('orders/create/{table}', [WaiterOrderController::class, 'create'])->name('orders.create'); // Start order for table
    Route::post('orders', [WaiterOrderController::class, 'store'])->name('orders.store'); // Save initial order
    Route::get('orders/{order}', [WaiterOrderController::class, 'show'])->name('orders.show'); // View/Modify order
    Route::post('orders/{order}/add-item', [WaiterOrderController::class, 'addItem'])->name('orders.add-item');
    Route::patch('orders/{order}/item/{orderItem}', [WaiterOrderController::class, 'updateItem'])->name('orders.update-item');
    Route::delete('orders/{order}/item/{orderItem}', [WaiterOrderController::class, 'removeItem'])->name('orders.remove-item');
    Route::post('orders/{order}/print-kitchen', [WaiterOrderController::class, 'printToKitchen'])->name('orders.print-kitchen');
    Route::post('orders/{order}/item/{orderItem}/cancel-print', [WaiterOrderController::class, 'cancelItemAndPrintNotification'])->name('orders.item.cancel-print');
    Route::post('orders/{order}/cancel', [WaiterOrderController::class, 'cancel'])->name('orders.cancel');
});

// Shared Notification Routes (could be placed elsewhere too)
Route::middleware('auth')->group(function() {
    Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
});

Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Inventory
    Route::resource('inventory-items', InventoryItemController::class);
    Route::resource('suppliers', SupplierController::class)->except(['show']); // Show might not be needed for supplier

    // Stock Management (Adding stock, wastage etc for a specific item)
     Route::get('inventory-items/{inventoryItem}/stock/create', [StockTransactionController::class, 'create'])->name('stock.create');
    Route::post('inventory-items/{inventoryItem}/stock', [StockTransactionController::class, 'store'])->name('stock.store');
    Route::get('inventory-items/{inventoryItem}/stock-history', [StockTransactionController::class, 'history'])->name('stock.history');


    // Expenses
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class);

    // Reports
    Route::get('reports/expenses', [ReportController::class, 'monthlyExpenses'])->name('reports.expenses');
    Route::get('reports/sales/summary', [ReportController::class, 'salesSummary'])->name('reports.sales.summary');
    Route::get('reports/sales/by-item', [ReportController::class, 'salesByItem'])->name('reports.sales.by_item');
    Route::get('reports/sales/by-category', [ReportController::class, 'salesByCategory'])->name('reports.sales.by_category');
    Route::get('reports/sales/by-waiter', [ReportController::class, 'salesByWaiter'])->name('reports.sales.by_waiter');
    Route::get('reports/inventory/valuation', [ReportController::class, 'inventoryValuation'])->name('reports.inventory.valuation');
    Route::get('reports/inventory/low-stock', [ReportController::class, 'lowStockReport'])->name('reports.inventory.low_stock');
    Route::get('reports/purchases/by-supplier', [ReportController::class, 'purchasesBySupplier'])->name('reports.purchases.by_supplier');
});

Route::middleware(['auth', 'reception'])->prefix('reception')->name('reception.')->group(function () {
    Route::get('tables', [ReceptionDashboardController::class, 'getTables'])->name('tables.api');
    Route::get('orders', [ReceptionDashboardController::class, 'getOrders'])->name('orders.api');
});