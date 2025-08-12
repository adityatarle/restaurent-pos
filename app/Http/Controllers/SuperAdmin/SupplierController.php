<?php

namespace App\Http\Controllers\SuperAdmin; // <--- Must be this

use App\Http\Controllers\Controller; // Import base controller
use App\Models\Supplier;
use Illuminate\Http\Request;
// ... other necessary imports

class SupplierController extends Controller
{
    // Your controller methods (index, create, store, edit, update, destroy)
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(15);
        return view('superadmin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('superadmin.suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:suppliers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return redirect()->route('superadmin.suppliers.index')
                         ->with('success', 'Supplier created successfully.');
    }

    // We excluded 'show' in the routes, so it's not strictly needed here unless you add it back
    // public function show(Supplier $supplier)
    // {
    //     return view('superadmin.suppliers.show', compact('supplier'));
    // }

    public function edit(Supplier $supplier)
    {
        return view('superadmin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('superadmin.suppliers.index')
                         ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Add any pre-deletion checks here if necessary
        // For example, if suppliers are linked to stock transactions and you don't want to delete them.
        // if ($supplier->stockTransactions()->exists()) {
        //     return back()->with('error', 'Cannot delete supplier with associated stock transactions.');
        // }
        $supplier->delete();

        return redirect()->route('superadmin.suppliers.index')
                         ->with('success', 'Supplier deleted successfully.');
    }
}