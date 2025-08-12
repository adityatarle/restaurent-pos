<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransactionController extends Controller // Renamed class
{
    public function create(InventoryItem $inventoryItem)
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('superadmin.inventory.stock.create', compact('inventoryItem', 'suppliers'));
    }

    public function store(Request $request, InventoryItem $inventoryItem)
    {
        $validated = $request->validate([
            'type' => 'required|in:purchase,wastage,manual_adjustment_in,manual_adjustment_out',
            'quantity' => 'required|numeric|min:0.001',
            'cost_price_at_transaction' => 'nullable|numeric|min:0|required_if:type,purchase',
            'supplier_id' => 'nullable|exists:suppliers,id|required_if:type,purchase',
            'notes' => 'nullable|string|max:500',
            'transaction_date' => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $inventoryItem) { // Removed $request from use() as it's not directly used inside
            $quantityChange = $validated['quantity'];
            if (in_array($validated['type'], ['wastage', 'manual_adjustment_out'])) {
                $quantityChange = -$quantityChange;
            }

            if ($quantityChange < 0 && ($inventoryItem->current_stock + $quantityChange < 0)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                   'quantity' => 'Not enough stock to deduct ' . abs($quantityChange) . ' ' . $inventoryItem->unit_of_measure . '. Current stock: ' . $inventoryItem->current_stock,
                ]);
            }

            StockTransaction::create([
                'inventory_item_id' => $inventoryItem->id,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'type' => $validated['type'],
                'quantity' => $quantityChange,
                'cost_price_at_transaction' => $validated['cost_price_at_transaction'] ?? null,
                'notes' => $validated['notes'],
                'transaction_date' => $validated['transaction_date'],
                'user_id' => auth()->id(),
            ]);

            $inventoryItem->increment('current_stock', $quantityChange);

            if ($validated['type'] == 'purchase' && isset($validated['cost_price_at_transaction']) && $inventoryItem->current_stock > 0) {
                // Original stock before this purchase
                $originalStock = $inventoryItem->current_stock - $validated['quantity']; // current_stock is already updated
                $originalTotalValue = $originalStock * ($inventoryItem->average_cost_price ?? 0);
                
                $purchaseValue = $validated['quantity'] * $validated['cost_price_at_transaction'];
                
                $newTotalValue = $originalTotalValue + $purchaseValue;
                $newTotalStock = $inventoryItem->current_stock; // This is the stock after purchase

                if ($newTotalStock > 0) { // Avoid division by zero
                   $inventoryItem->average_cost_price = $newTotalValue / $newTotalStock;
                   $inventoryItem->save();
                } elseif ($newTotalStock == 0 && $validated['quantity'] > 0) { // If this purchase brought stock from 0
                    $inventoryItem->average_cost_price = $validated['cost_price_at_transaction'];
                    $inventoryItem->save();
                }
            }
        });

        return redirect()->route('superadmin.inventory-items.index')->with('success', 'Stock updated successfully for ' . $inventoryItem->name);
    }

    public function history(InventoryItem $inventoryItem)
    {
        $transactions = $inventoryItem->stockTransactions()->with(['supplier', 'user'])->latest('transaction_date')->paginate(20);
        return view('superadmin.inventory.stock.history', compact('inventoryItem', 'transactions'));
    }
}