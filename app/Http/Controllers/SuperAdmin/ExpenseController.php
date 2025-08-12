<?php

namespace App\Http\Controllers\SuperAdmin; // <--- CORRECT NAMESPACE

use App\Http\Controllers\Controller; // <--- IMPORT BASE CONTROLLER
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // For receipt uploads

class ExpenseController extends Controller // <--- CORRECT CLASS DEFINITION
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'user')->latest();

        if ($request->filled('month_year')) {
            $parts = explode('-', $request->month_year);
            if (count($parts) == 2) {
                $query->whereYear('expense_date', $parts[0])->whereMonth('expense_date', $parts[1]);
            }
        }
        if($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        $expenses = $query->paginate(15);
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('superadmin.expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('superadmin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor_name' => 'nullable|string|max:255',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $validated['receipt_url'] = $path;
        }

        Expense::create($validated);
        return redirect()->route('superadmin.expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('superadmin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
         $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'vendor_name' => 'nullable|string|max:255',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_url) {
                Storage::disk('public')->delete($expense->receipt_url);
            }
            $path = $request->file('receipt')->store('receipts', 'public');
            $validated['receipt_url'] = $path;
        }

        $expense->update($validated);
        return redirect()->route('superadmin.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_url) {
            Storage::disk('public')->delete($expense->receipt_url);
        }
        $expense->delete();
        return redirect()->route('superadmin.expenses.index')->with('success', 'Expense deleted.');
    }
}