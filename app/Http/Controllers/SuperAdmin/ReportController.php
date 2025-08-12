<?php

namespace App\Http\Controllers\SuperAdmin; // <--- CORRECT NAMESPACE

use App\Http\Controllers\Controller; // <--- IMPORT BASE CONTROLLER
use App\Models\Expense;
// use App\Models\ExpenseCategory; // Not strictly needed if joining
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

    // You can add other report methods here, for example:
    // public function lowStockReport() { /* ... */ }
    // public function salesReport() { /* ... */ }
}