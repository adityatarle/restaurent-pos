<?php

namespace App\Http\Controllers\Reception; // Ensure this namespace is correct

use App\Http\Controllers\Controller; // Make sure to use the base Controller
use Illuminate\Http\Request;
use App\Models\RestaurantTable;
use App\Models\Order;
use Illuminate\Support\Facades\Auth; // If you need Auth facade in this controller

class ReceptionDashboardController extends Controller
{
    /**
     * Display the reception dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() // <<<< THIS IS THE METHOD YOU NEED TO ADD
    {
        // Fetch data needed for the reception dashboard
        $tables = RestaurantTable::with('currentOrder.waiter')->orderBy('name')->get();

        $ongoingOrders = Order::whereNotIn('status', ['paid', 'cancelled'])
                            ->with('restaurantTable', 'waiter')
                            ->orderBy('updated_at', 'desc')
                            ->get();

        // You might also want to fetch recent notifications for the logged-in reception user
        $notifications = collect(); // Placeholder
        if (Auth::check()) {
             $notifications = Auth::user()->notifications()->where('is_read', false)->latest()->take(10)->get();
        }


        // Return the view for the reception dashboard, passing the data
        return view('reception.dashboard', compact('tables', 'ongoingOrders', 'notifications'));
    }

    public function getTables()
{
    $tables = RestaurantTable::with('currentOrder.waiter')->orderBy('name')->get()->map(function ($table) {
        return [
            'id' => $table->id,
            'name' => $table->name,
            'status' => $table->status,
            'current_order' => $table->currentOrder ? [
                'id' => $table->currentOrder->id,
                'waiter_name' => $table->currentOrder->waiter->name,
                'customer_count' => $table->currentOrder->customer_count,
                'status' => $table->currentOrder->status,
            ] : null,
        ];
    });

    return response()->json($tables);
}

public function getOrders()
{
    $orders = Order::whereNotIn('status', ['paid', 'cancelled'])
        ->with('restaurantTable', 'waiter')
        ->orderBy('updated_at', 'desc')
        ->get()
        ->map(function ($order) {
            return [
                'id' => $order->id,
                'table_name' => $order->restaurantTable->name,
                'waiter_name' => $order->waiter->name,
                'customer_count' => $order->customer_count,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'updated_at' => $order->updated_at->toDateTimeString(),
            ];
        });

    return response()->json($orders);
}

    // You can add other methods here if needed for other reception dashboard functionalities
}