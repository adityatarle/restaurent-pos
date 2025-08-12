<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = InventoryItem::latest()->paginate(15);
        return view('superadmin.inventory.items.index', compact('items'));
    }

    public function create()
    {
        return view('superadmin.inventory.items.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_items,name',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'reorder_level' => 'nullable|numeric|min:0',
            'average_cost_price' => 'nullable|numeric|min:0',
            'opening_stock' => 'nullable|numeric|min:0', // For initial stock
            'opening_stock_cost' => 'nullable|numeric|min:0|required_with:opening_stock', // if opening stock given, cost is needed
        ]);

        $item = InventoryItem::create($validated);

        if (!empty($validated['opening_stock']) && $validated['opening_stock'] > 0) {
            $item->stockTransactions()->create([
                'type' => 'opening_stock',
                'quantity' => $validated['opening_stock'],
                'cost_price_at_transaction' => $validated['opening_stock_cost'] ?? $validated['average_cost_price'],
                'transaction_date' => now(),
                'user_id' => auth()->id(),
                'notes' => 'Initial opening stock',
            ]);
            $item->update(['current_stock' => $validated['opening_stock']]);
        }


        return redirect()->route('superadmin.inventory-items.index')->with('success', 'Inventory item added successfully.');
    }

    public function edit(InventoryItem $inventoryItem)
    {
        return view('superadmin.inventory.items.edit', ['item' => $inventoryItem]);
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:inventory_items,name,' . $inventoryItem->id,
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'reorder_level' => 'nullable|numeric|min:0',
            'average_cost_price' => 'nullable|numeric|min:0',
        ]);

        $inventoryItem->update($validated);
        return redirect()->route('superadmin.inventory-items.index')->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        // Consider if soft delete is enough or if associated stock transactions should be handled
        if ($inventoryItem->stockTransactions()->count() > 0) {
             return back()->with('error', 'Cannot delete item with stock history. Consider deactivating or archiving.');
        }
        $inventoryItem->delete(); // or $inventoryItem->forceDelete();
        return redirect()->route('superadmin.inventory-items.index')->with('success', 'Inventory item deleted.');
    }
}
