<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\RestaurantTable;
use App\Models\KitchenPrint;
use App\Services\PrinterService;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\OrderUpdated; // Add this

class OrderController extends Controller
{
    public function cancel(Request $request, Order $order)
    {
        if (in_array($order->status, ['paid', 'cancelled'])) {
            return back()->with('info', 'Order already finalised.');
        }

        // Print cancellation for any already-printed items
        $printedItems = $order->orderItems()->where('printed_to_kitchen', true)->where('status', '!=', 'cancelled')->with('menuItem')->get();
        if ($printedItems->isNotEmpty()) {
            $items = $printedItems->map(function($i) { return ['name' => $i->menuItem->name, 'quantity' => $i->quantity]; })->toArray();
            app(\App\Services\PrinterService::class)->printKitchenCancellation($order, $items);
        }

        // Mark all items cancelled
        $order->orderItems()->update(['status' => 'cancelled']);

        // Cancel order and free table
        $order->status = 'cancelled';
        $order->save();
        if ($order->restaurantTable) {
            $order->restaurantTable->status = 'available';
            $order->restaurantTable->save();
            event(new \App\Events\TableAssigned($order->restaurantTable, null));
        }

        // Notify Reception on full order cancellation
        $receptionUsers = \App\Models\User::where('role', 'reception')->orWhere('role', 'superadmin')->get();
        foreach ($receptionUsers as $receptor) {
            \App\Models\Notification::create([
                'user_id' => $receptor->id,
                'type' => 'order_cancelled',
                'message' => "Order #{$order->id} cancelled for Table {$order->restaurantTable?->name}",
                'link' => route('reception.dashboard'),
            ]);
            event(new \App\Events\NotificationPushed(
                "Order #{$order->id} cancelled for Table {$order->restaurantTable?->name}",
                route('reception.dashboard')
            ));
        }

        // Broadcast order update
        event(new \App\Events\OrderUpdated($order));

        return redirect()->route('waiter.dashboard')->with('success', "Order #{$order->id} cancelled.");
    }
    public function show(Order $order)
    {
        $order->load('orderItems.menuItem', 'restaurantTable');
        $categories = Category::with(['menuItems' => function ($query) {
            $query->where('is_available', true)->orderBy('name');
        }])->orderBy('name')->get();

        return view('waiter.orders.show', compact('order', 'categories'));
    }

    public function create(RestaurantTable $table)
    {
        $existing = $table->currentOrder()->first();
        if ($existing) {
            return redirect()->route('waiter.orders.show', $existing);
        }
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'user_id' => Auth::id(),
            'customer_count' => request('customer_count', 1),
            'status' => 'pending',
        ]);
        $table->status = 'occupied';
        $table->save();
        event(new \App\Events\TableAssigned($table, $order));
        // Notify reception that a new order started
        event(new \App\Events\NotificationPushed(
            "New order #{$order->id} started at Table {$table->name} (Guests: {$order->customer_count}).",
            route('reception.dashboard')
        ));
        return redirect()->route('waiter.orders.show', $order)->with('success', 'Order started for table.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_table_id' => 'required|exists:restaurant_tables,id',
            'customer_count' => 'required|integer|min:1',
        ]);
        $table = RestaurantTable::findOrFail($data['restaurant_table_id']);
        $existing = $table->currentOrder()->first();
        if ($existing) {
            return redirect()->route('waiter.orders.show', $existing);
        }
        $order = Order::create([
            'restaurant_table_id' => $table->id,
            'user_id' => Auth::id(),
            'customer_count' => $data['customer_count'],
            'status' => 'pending',
        ]);
        $table->status = 'occupied';
        $table->save();
        event(new \App\Events\TableAssigned($table, $order));
        return redirect()->route('waiter.orders.show', $order)->with('success', 'Order created.');
    }

    public function addItem(Request $request, Order $order)
    {
        $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'item_notes' => 'nullable|string|max:255',
        ]);

        if ($order->status === 'paid' || $order->status === 'cancelled') {
            return back()->with('error', 'Cannot add items to a completed or cancelled order.');
        }

        $menuItem = MenuItem::findOrFail($request->menu_item_id);

        // Check if item already exists in order (and is not cancelled) to update quantity
        $existingItem = $order->orderItems()
            ->where('menu_item_id', $menuItem->id)
            ->where('status', '!=', 'cancelled') // Consider items not yet cancelled
            // Optional: Add more conditions if you want to group by notes, etc.
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $request->quantity;
            if ($request->filled('item_notes')) { // Overwrite or append notes based on logic
                $existingItem->item_notes = $existingItem->item_notes ? $existingItem->item_notes . '; ' . $request->item_notes : $request->item_notes;
            }
            $existingItem->status = 'pending'; // Reset status if it was modified before
            $existingItem->printed_to_kitchen = false; // Needs to be re-printed or part of new print
            $existingItem->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $request->quantity,
                'price_at_order' => $menuItem->price,
                'item_notes' => $request->item_notes,
                'status' => 'pending', // Default status
                'printed_to_kitchen' => false,
            ]);
        }

        $order->calculateTotal();
        event(new OrderUpdated($order)); // Trigger event
        return back()->with('success', 'Item added to order.');
    }

    public function updateItem(Request $request, Order $order, OrderItem $orderItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1', // Or min:0 if allowing cancellation via quantity 0
        ]);

        if ($order->id !== $orderItem->order_id) {
            abort(403, "Item does not belong to this order.");
        }
        if ($order->status === 'paid' || $order->status === 'cancelled') {
            return back()->with('error', 'Cannot modify items in a completed or cancelled order.');
        }

        $originalQuantity = $orderItem->quantity;
        $orderItem->quantity = $request->quantity;
        $orderItem->status = 'pending'; // Mark as pending to be re-printed/notified
        $orderItem->printed_to_kitchen = false;
        $orderItem->save();

        $order->calculateTotal();
        event(new OrderUpdated($order)); // Trigger event
        return back()->with('success', 'Item quantity updated.');
    }




    public function removeItem(Order $order, OrderItem $orderItem) // This is logical cancel
    {
        if ($order->id !== $orderItem->order_id) {
            abort(403, "Item does not belong to this order.");
        }
        if ($order->status === 'paid' || $order->status === 'cancelled') {
            return back()->with('error', 'Cannot remove items from a completed or cancelled order.');
        }

        $orderItem->status = 'cancelled'; // Mark as cancelled
        // $orderItem->quantity = 0; // Optional: zero out quantity
        $orderItem->save();

        $order->calculateTotal();
        event(new OrderUpdated($order)); // Trigger event

        // If item was already sent to kitchen, a cancellation print/notification is needed
        if ($orderItem->printed_to_kitchen) {
            KitchenPrint::create([
                'order_id' => $order->id,
                'type' => 'cancel_item',
                'print_content' => [
                    'item_id' => $orderItem->id,
                    'menu_item_name' => $orderItem->menuItem->name,
                    'original_quantity' => $orderItem->quantity, // The quantity that was cancelled
                ],
                'user_id' => Auth::id(),
            ]);

            // Send cancellation to printer stub
            app(\App\Services\PrinterService::class)->printKitchenCancellation($order, [[
                'name' => $orderItem->menuItem->name,
                'quantity' => $orderItem->quantity,
            ]]);

            // Notify Reception
            $receptionUsers = User::where('role', 'reception')->orWhere('role', 'superadmin')->get();
            foreach ($receptionUsers as $receptor) {
                Notification::create([
                    'user_id' => $receptor->id,
                    'type' => 'order_item_cancelled',
                    'message' => "Item '{$orderItem->menuItem->name}' cancelled from Order #{$order->id} for Table {$order->restaurantTable->name}.",
                    'link' => route('reception.dashboard'),
                ]);
                event(new \App\Events\NotificationPushed(
                    "Item '{$orderItem->menuItem->name}' cancelled from Order #{$order->id} for Table {$order->restaurantTable->name}.",
                    route('reception.dashboard')
                ));
            }
            // This would be a "Cancel Print" to kitchen
            // For simulation: return redirect()->route('some.print.preview.cancel', ['order' => $order, 'item' => $orderItem]);
        }

        return back()->with('success', 'Item cancelled from order.');
    }

    public function cancelItemAndPrintNotification(Order $order, OrderItem $orderItem)
    {
        // Ensure the item gets cancelled and a cancellation ticket is printed (handled in removeItem)
        return $this->removeItem($order, $orderItem);
    }

    public function printToKitchen(Request $request, Order $order, PrinterService $printer) 
    {
        // Logic to determine what needs printing (new items, modified quantities)
        // For this example, let's assume we print items not yet marked `printed_to_kitchen = true`
        // or items whose status implies they need re-printing.

        DB::beginTransaction();
        try {
            $itemsToPrint = $order->orderItems()
                ->where('printed_to_kitchen', false)
                ->where('status', '!=', 'cancelled') // Don't print cancelled items as new
                ->with('menuItem')
                ->get();

            if ($itemsToPrint->isEmpty() && !$request->input('force_reprint_all')) { // Add a way to reprint all if needed
                // Check for items that were modified and need re-printing (status changed, etc.)
                // This logic can get complex depending on requirements.
                // For now, if nothing explicitly new, assume no print needed unless forced.
                return back()->with('info', 'No new items to print for the kitchen.');
            }

            $printContent = $itemsToPrint->map(function ($item) {
                return [
                    'name' => $item->menuItem->name,
                    'quantity' => $item->quantity,
                    'notes' => $item->item_notes,
                ];
            })->toArray();

            if (empty($printContent) && !$request->input('force_reprint_all')) {
                return back()->with('info', 'No items to print for the kitchen.');
            }
            if ($request->input('force_reprint_all')) {
                $allItems = $order->orderItems()->where('status', '!=', 'cancelled')->with('menuItem')->get();
                $printContent = $allItems->map(function ($item) {
                    return [
                        'name' => $item->menuItem->name,
                        'quantity' => $item->quantity,
                        'notes' => $item->item_notes,
                    ];
                })->toArray();
            }


            // Record the print job
            KitchenPrint::create([
                'order_id' => $order->id,
                'type' => $order->kitchenPrints()->count() == 0 ? 'new_order' : 'add_item', // Or 'modify_order'
                'print_content' => $printContent,
                'user_id' => Auth::id(),
            ]);

            // Send to printer stub
            $printer->printKitchenTicket($order, $printContent);

            // Mark items as printed
            foreach ($itemsToPrint as $item) {
                $item->printed_to_kitchen = true;
                $item->status = 'sent_to_kitchen'; // Update status
                $item->save();
            }
            if ($request->input('force_reprint_all')) {
                $order->orderItems()->where('status', '!=', 'cancelled')->update([
                    'printed_to_kitchen' => true,
                    'status' => 'sent_to_kitchen'
                ]);
            }


            if ($order->status == 'pending') { // First print
                $order->status = 'preparing';
                $order->save();
            }

            DB::commit();
            event(new OrderUpdated($order)); // Trigger event

            // In a real app, this would trigger a physical print.
            // For now, show a preview or just a success message.
            // We can pass $printContent to a view for "print preview".
            session()->flash('kitchen_print_content', $printContent);
            session()->flash('kitchen_print_order_id', $order->id);
            session()->flash('kitchen_print_table_name', $order->restaurantTable->name);


            return back()->with('success', 'Order sent to kitchen!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Kitchen Print Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send order to kitchen. ' . $e->getMessage());
        }
    }
}
