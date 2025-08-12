<?php
namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RestaurantTable;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Events\TableAssigned;

class WaiterDashboardController extends Controller
{
    public function index()
    {
        $tables = RestaurantTable::with('currentOrder.waiter')->orderBy('name')->get();
        return view('waiter.dashboard', compact('tables'));
    }

    public function assignTable(Request $request, RestaurantTable $table)
    {
        $request->validate(['customer_count' => 'required|integer|min:1']);

        if ($table->status === 'occupied' && $table->currentOrder) {
            return back()->with('error', 'Table is already occupied.');
        }

        // Create a new pending order
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'user_id' => Auth::id(),
            'customer_count' => $request->customer_count,
            'status' => 'pending',
        ]);

        $table->status = 'occupied';
        $table->save();

        // Dispatch TableAssigned event
        event(new TableAssigned($table, $order));

        return redirect()->route('waiter.orders.show', $order)->with('success', 'Table assigned. Start taking the order.');
    }
}