<?php

namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\TableAssigned;
use App\Events\OrderUpdated;

class TableController extends Controller
{
    public function vacate(Request $request, RestaurantTable $table)
    {
        $cancelCurrentOrder = (bool) $request->input('cancel_order', false);

        $currentOrder = $table->currentOrder()->first();
        if ($currentOrder && $cancelCurrentOrder) {
            // Cancel the order explicitly
            $currentOrder->status = 'cancelled';
            $currentOrder->save();
            event(new OrderUpdated($currentOrder));
        }

        // Free the table
        $table->status = 'available';
        $table->save();

        // Broadcast table update
        event(new TableAssigned($table, null));

        return back()->with('success', "Table {$table->name} vacated" . ($cancelCurrentOrder && $currentOrder ? ", Order #{$currentOrder->id} cancelled" : ''));
    }
}