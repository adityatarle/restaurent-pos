<?php

namespace App\Http\Controllers\SuperAdmin; // <--- CORRECT NAMESPACE

use App\Http\Controllers\Controller; // <--- IMPORT BASE CONTROLLER
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // For DB::raw

class ReportController extends Controller // <--- CORRECT CLASS DEFINITION
{
    public function monthlyExpenses(Request $request)
    {
        $selectedMonthYear = $request->input('month_year', now()->format('Y-m'));
        list($year, $month) = explode('-', $selectedMonthYear);

        $expensesByCategory = Expense::whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name as category_name', DB::raw('SUM(expenses.amount) as total_amount'))
            ->groupBy('expense_categories.name') // or 'expense_categories.id', 'expense_categories.name'
            ->orderBy('category_name')
            ->get();

        $totalExpenses = $expensesByCategory->sum('total_amount');

        return view('superadmin.reports.expenses_monthly', compact('expensesByCategory', 'totalExpenses', 'selectedMonthYear'));
    }

    public function salesSummary(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);

        $paidOrders = Order::where('status', 'paid')
            ->whereBetween('updated_at', [$start, $end])
            ->get();

        $totalRevenue = (float) $paidOrders->sum('total_amount');
        $orderCount = $paidOrders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0.0;

        $cancelledCount = Order::where('status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $topItems = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * price_at_order) as revenue'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->where('order_items.status', '!=', 'cancelled')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->groupBy('menu_item_id')
            ->orderByDesc('qty')
            ->with('menuItem')
            ->limit(10)
            ->get();

        if ($request->boolean('export')) {
            $rows = [];
            $rows[] = ['Metric', 'Value'];
            $rows[] = ['Total Revenue', $totalRevenue];
            $rows[] = ['Order Count', $orderCount];
            $rows[] = ['Avg Order Value', round($avgOrderValue, 2)];
            $rows[] = ['Cancelled Orders', $cancelledCount];
            $rows[] = [];
            $rows[] = ['Top Items'];
            $rows[] = ['Item', 'Qty', 'Revenue'];
            foreach ($topItems as $ti) {
                $rows[] = [$ti->menuItem?->name ?? ('#'.$ti->menu_item_id), $ti->qty, round($ti->revenue, 2)];
            }
            return $this->csvResponse('sales_summary.csv', $rows);
        }

        return view('superadmin.reports.sales_summary', compact('start', 'end', 'totalRevenue', 'orderCount', 'avgOrderValue', 'cancelledCount', 'topItems'));
    }

    public function salesByItem(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);
        $items = OrderItem::select('menu_item_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * price_at_order) as revenue'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->where('order_items.status', '!=', 'cancelled')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->groupBy('menu_item_id')
            ->with('menuItem.category')
            ->orderByDesc('revenue')
            ->get();

        if ($request->boolean('export')) {
            $rows = [['Item', 'Category', 'Qty', 'Revenue']];
            foreach ($items as $row) {
                $rows[] = [
                    $row->menuItem?->name ?? ('#'.$row->menu_item_id),
                    $row->menuItem?->category?->name ?? '',
                    $row->qty,
                    round($row->revenue, 2)
                ];
            }
            return $this->csvResponse('sales_by_item.csv', $rows);
        }

        return view('superadmin.reports.sales_by_item', compact('items', 'start', 'end'));
    }

    public function salesByCategory(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);
        $rows = OrderItem::select('categories.id as category_id', 'categories.name as category_name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.quantity * order_items.price_at_order) as revenue'))
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->join('categories', 'categories.id', '=', 'menu_items.category_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->where('order_items.status', '!=', 'cancelled')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        if ($request->boolean('export')) {
            $csv = [['Category', 'Qty', 'Revenue']];
            foreach ($rows as $r) {
                $csv[] = [$r->category_name, $r->qty, round($r->revenue, 2)];
            }
            return $this->csvResponse('sales_by_category.csv', $csv);
        }

        return view('superadmin.reports.sales_by_category', compact('rows', 'start', 'end'));
    }

    public function salesByWaiter(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);
        $rows = Order::select('users.id as user_id', 'users.name as waiter_name', DB::raw('COUNT(orders.id) as orders_count'), DB::raw('SUM(orders.total_amount) as revenue'))
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->get();

        if ($request->boolean('export')) {
            $csv = [['Waiter', 'Orders', 'Revenue', 'Avg Order']];
            foreach ($rows as $r) {
                $avg = $r->orders_count > 0 ? ($r->revenue / $r->orders_count) : 0;
                $csv[] = [$r->waiter_name, $r->orders_count, round($r->revenue, 2), round($avg, 2)];
            }
            return $this->csvResponse('sales_by_waiter.csv', $csv);
        }

        return view('superadmin.reports.sales_by_waiter', compact('rows', 'start', 'end'));
    }

    public function inventoryValuation(Request $request)
    {
        $items = InventoryItem::orderBy('name')->get()->map(function ($i) {
            $value = ($i->current_stock ?? 0) * ($i->average_cost_price ?? 0);
            return [
                'item' => $i,
                'value' => $value,
            ];
        });
        $totalValue = $items->sum('value');

        if ($request->boolean('export')) {
            $csv = [['Item', 'Stock', 'Avg Cost', 'Value']];
            foreach ($items as $row) {
                $csv[] = [
                    $row['item']->name,
                    $row['item']->current_stock,
                    round($row['item']->average_cost_price, 2),
                    round($row['value'], 2),
                ];
            }
            return $this->csvResponse('inventory_valuation.csv', $csv);
        }

        return view('superadmin.reports.inventory_valuation', compact('items', 'totalValue'));
    }

    public function lowStockReport(Request $request)
    {
        $items = InventoryItem::whereNotNull('reorder_level')
            ->whereColumn('current_stock', '<=', 'reorder_level')
            ->orderBy('name')
            ->get();

        if ($request->boolean('export')) {
            $csv = [['Item', 'Stock', 'UOM', 'Reorder Level']];
            foreach ($items as $i) {
                $csv[] = [$i->name, $i->current_stock, $i->unit_of_measure, $i->reorder_level];
            }
            return $this->csvResponse('low_stock.csv', $csv);
        }

        return view('superadmin.reports.low_stock', compact('items'));
    }

    public function purchasesBySupplier(Request $request)
    {
        [$start, $end] = $this->parseDateRange($request);

        $rows = StockTransaction::select('suppliers.id as supplier_id', 'suppliers.name as supplier_name', DB::raw('SUM(stock_transactions.quantity * stock_transactions.cost_price_at_transaction) as total_spent'))
            ->join('suppliers', 'suppliers.id', '=', 'stock_transactions.supplier_id')
            ->where('stock_transactions.type', 'purchase')
            ->whereBetween('stock_transactions.transaction_date', [$start, $end])
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_spent')
            ->get();

        if ($request->boolean('export')) {
            $csv = [['Supplier', 'Total Spent']];
            foreach ($rows as $r) {
                $csv[] = [$r->supplier_name, round($r->total_spent, 2)];
            }
            return $this->csvResponse('purchases_by_supplier.csv', $csv);
        }

        return view('superadmin.reports.purchases_by_supplier', compact('rows', 'start', 'end'));
    }

    private function parseDateRange(Request $request): array
    {
        $start = $request->input('start_date') ? now()->parse($request->input('start_date'))->startOfDay() : now()->subDays(30)->startOfDay();
        $end = $request->input('end_date') ? now()->parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
        return [$start, $end];
    }

    private function csvResponse(string $filename, array $rows)
    {
        $content = '';
        foreach ($rows as $row) {
            $escaped = array_map(function ($v) {
                $v = (string) $v;
                if (str_contains($v, '"') || str_contains($v, ',') || str_contains($v, "\n")) {
                    $v = '"' . str_replace('"', '""', $v) . '"';
                }
                return $v;
            }, $row);
            $content .= implode(',', $escaped) . "\n";
        }
        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}