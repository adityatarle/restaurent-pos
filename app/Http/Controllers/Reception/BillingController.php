<?php
namespace App\Http\Controllers\Reception;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Events\TableAssigned;
use App\Events\OrderUpdated;


class BillingController extends Controller
{
    public function generateBill(Order $order)
    {
        $order->load('orderItems.menuItem', 'restaurantTable', 'waiter');
        if ($order->status === 'paid') {
            // Optionally allow re-printing paid bill
        }
        // Calculate total if not already accurate (should be done on item add/update)
        // $order->calculateTotal();

        return view('reception.billing.generate', compact('order'));
    }

  public function markAsPaid(Request $request, Order $order)
    {
        if ($order->status === 'paid') {
            return back()->with('info', 'Order already marked as paid.');
        }

        $order->status = 'paid';
        $order->completed_at = Carbon::now();
        $order->save();

        $table = $order->restaurantTable;
        if ($table) {
            $table->status = 'available';
            $table->save();
            event(new TableAssigned($table, null));
        }

        event(new OrderUpdated($order));
        event(new \App\Events\NotificationPushed(
            "Order #{$order->id} has been paid. Table {$table->name} is now available.",
            route('reception.dashboard')
        ));

        return redirect()->route('reception.dashboard')->with('success', "Order #{$order->id} marked as paid. Table {$table->name} is now available.");
    }
}