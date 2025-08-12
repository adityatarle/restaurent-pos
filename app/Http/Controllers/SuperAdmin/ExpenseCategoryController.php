<?php

namespace App\Http\Controllers\SuperAdmin; // <--- CORRECT NAMESPACE

use App\Http\Controllers\Controller; // <--- IMPORT BASE CONTROLLER
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller // <--- CORRECT CLASS DEFINITION
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ExpenseCategory::latest()->paginate(10);
        return view('superadmin.expenses.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.expenses.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('superadmin.expense-categories.index')
                         ->with('success', 'Expense category created successfully.');
    }

    /**
     * Display the specified resource.
     * We excluded 'show' in routes, so this might not be used.
     */
    // public function show(ExpenseCategory $expenseCategory)
    // {
    //     // return view('superadmin.expenses.categories.show', compact('expenseCategory'));
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('superadmin.expenses.categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('superadmin.expense-categories.index')
                         ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Add check if category is in use by any expenses
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category: It is currently assigned to one or more expenses.');
        }
        $expenseCategory->delete();

        return redirect()->route('superadmin.expense-categories.index')
                         ->with('success', 'Expense category deleted successfully.');
    }
}