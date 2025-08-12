<?php

namespace App\Http\Controllers\SuperAdmin; // Ensure this namespace is correct

use App\Http\Controllers\Controller;      // Make sure to use the base Controller
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

class RestaurantTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tables = RestaurantTable::orderBy('name')->paginate(10); // Get all tables, paginated
        return view('superadmin.tables.index', compact('tables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.tables.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:restaurant_tables,name',
            'capacity' => 'required|integer|min:1',
            'status' => 'sometimes|in:available,occupied,reserved', // Status might be managed by operations, not direct admin input usually
            'visual_coordinates' => 'nullable|string|max:255',
        ]);

        RestaurantTable::create($request->all());

        return redirect()->route('superadmin.tables.index')
                         ->with('success', 'Restaurant table created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RestaurantTable $table) // Route model binding
    {
        return view('superadmin.tables.show', compact('table'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RestaurantTable $table) // Route model binding
    {
        return view('superadmin.tables.edit', compact('table'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RestaurantTable $table) // Route model binding
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:restaurant_tables,name,'.$table->id,
            'capacity' => 'required|integer|min:1',
            'status' => 'sometimes|in:available,occupied,reserved',
            'visual_coordinates' => 'nullable|string|max:255',
        ]);

        $table->update($request->all());

        return redirect()->route('superadmin.tables.index')
                         ->with('success', 'Restaurant table updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RestaurantTable $table) // Route model binding
    {
        // Optional: Add checks if table is occupied and has orders before deleting
        if ($table->status === 'occupied' && $table->currentOrder) {
            return back()->with('error', 'Cannot delete an occupied table with an active order. Please clear the order first.');
        }
        // Optional: Add further checks if orders exist for this table at all (even past orders)
        // if ($table->orders()->exists()) {
        //    return back()->with('error', 'This table has past orders and cannot be deleted directly. Consider archiving or disabling.');
        // }

        $table->delete();
        return redirect()->route('superadmin.tables.index')
                         ->with('success', 'Restaurant table deleted successfully.');
    }
}