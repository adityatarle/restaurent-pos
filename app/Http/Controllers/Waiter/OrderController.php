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
        $tables = RestaurantTable::orderBy('name')->get();
        $openOrders = Order::whereNotIn('status', ['paid', 'cancelled'])
            ->where('id', '!=', $order->id)
            ->orderByDesc('id')
            ->get();

        return view('waiter.orders.show', compact('order', 'categories', 'tables', 'openOrders'));
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
            'course' => 'nullable|integer|min:1|max:10',
        ]);

        if ($order->status === 'paid' || $order->status === 'cancelled') {
            return back()->with('error', 'Cannot add items to a completed or cancelled order.');
        }

        $menuItem = MenuItem::with('ingredients')->findOrFail($request->menu_item_id);

        // Low-stock check based on ingredients
        foreach ($menuItem->ingredients as $ingredient) {
            $required = ($ingredient->pivot->quantity ?? 0) * $request->quantity;
            if (($ingredient->current_stock ?? 0) < $required) {
                // Optionally block or warn; here we warn and allow
                session()->flash('info', "Low stock: {$ingredient->name}. Required {$required} {$ingredient->unit_of_measure}, available {$ingredient->current_stock}.");
            }
        }

        // Check if item already exists in order (and is not cancelled) to update quantity
        $existingItem = $order->orderItems()
            ->where('menu_item_id', $menuItem->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $request->quantity;
            if ($request->filled('item_notes')) {
                $existingItem->item_notes = $existingItem->item_notes ? $existingItem->item_notes . '; ' . $request->item_notes : $request->item_notes;
            }
            if ($request->filled('course')) { $existingItem->course = $request->course; }
            $existingItem->status = 'pending';
            $existingItem->printed_to_kitchen = false;
            $existingItem->save();
        } else {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $request->quantity,
                'price_at_order' => $menuItem->price,
                'item_notes' => $request->item_notes,
                'course' => $request->course,
                'status' => 'pending',
                'printed_to_kitchen' => false,
            ]);
        }

        $order->calculateTotal();
        event(new OrderUpdated($order));
        return back()->with('success', 'Item added to order.');
    }

    public function updateItem(Request $request, Order $order, OrderItem $orderItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($order->id !== $orderItem->order_id) {
            abort(403, "Item does not belong to this order.");
        }
        if ($order->status === 'paid' || $order->status === 'cancelled') {
            return back()->with('error', 'Cannot modify items in a completed or cancelled order.');
        }

        $orderItem->quantity = $request->quantity;
        $orderItem->status = 'pending';
        $orderItem->printed_to_kitchen = false;
        $orderItem->save();

        $order->calculateTotal();
        event(new OrderUpdated($order));
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

        $orderItem->status = 'cancelled';
        $orderItem->save();

        $order->calculateTotal();
        event(new OrderUpdated($order));

        if ($orderItem->printed_to_kitchen) {
            KitchenPrint::create([
                'order_id' => $order->id,
                'type' => 'cancel_item',
                'print_content' => [
                    'item_id' => $orderItem->id,
                    'menu_item_name' => $orderItem->menuItem->name,
                    'original_quantity' => $orderItem->quantity,
                ],
                'user_id' => Auth::id(),
            ]);

            app(\App\Services\PrinterService::class)->printKitchenCancellation($order, [[
                'name' => $orderItem->menuItem->name,
                'quantity' => $orderItem->quantity,
            ]]);

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
        }

        return back()->with('success', 'Item cancelled from order.');
    }

    public function cancelItemAndPrintNotification(Order $order, OrderItem $orderItem)
    {
        return $this->removeItem($order, $orderItem);
    }

    public function fireItem(Order $order, OrderItem $orderItem)
    {
        if ($order->id !== $orderItem->order_id) abort(403);
        $orderItem->fired_at = now();
        $orderItem->status = 'fired';
        $orderItem->save();
        event(new OrderUpdated($order));
        return back()->with('success', 'Item fired to kitchen.');
    }

    public function holdItem(Order $order, OrderItem $orderItem)
    {
        if ($order->id !== $orderItem->order_id) abort(403);
        $orderItem->status = 'hold';
        $orderItem->save();
        event(new OrderUpdated($order));
        return back()->with('success', 'Item put on hold.');
    }

    public function transferItem(Request $request, Order $order, OrderItem $orderItem)
    {
        $request->validate(['target_order_id' => 'required|exists:orders,id']);
        if ($order->id !== $orderItem->order_id) abort(403);
        $target = Order::findOrFail($request->target_order_id);
        $orderItem->order_id = $target->id;
        $orderItem->save();
        $order->calculateTotal();
        $target->calculateTotal();
        event(new OrderUpdated($order));
        event(new OrderUpdated($target));
        return back()->with('success', 'Item transferred.');
    }

    public function splitOrder(Request $request, Order $order)
    {
        $request->validate(['item_ids' => 'required|array', 'item_ids.*' => 'exists:order_items,id']);
        $child = Order::create([
            'restaurant_table_id' => $order->restaurant_table_id,
            'user_id' => Auth::id(),
            'customer_count' => $order->customer_count,
            'status' => 'pending',
            'parent_order_id' => $order->id,
        ]);
        // Move selected items
        OrderItem::whereIn('id', $request->item_ids)->where('order_id', $order->id)->update(['order_id' => $child->id]);
        $order->calculateTotal();
        $child->calculateTotal();
        event(new OrderUpdated($order));
        event(new OrderUpdated($child));
        return redirect()->route('waiter.orders.show', $child)->with('success', 'Split bill created.');
    }

    public function printToKitchen(Request $request, Order $order, PrinterService $printer) 
    {
        DB::beginTransaction();
        try {
            $itemsToPrint = $order->orderItems()
                ->where('printed_to_kitchen', false)
                ->where('status', '!=', 'cancelled')
                ->with('menuItem')
                ->get();

            if ($itemsToPrint->isEmpty() && !$request->input('force_reprint_all')) {
                return back()->with('info', 'No new items to print for the kitchen.');
            }

            $printContent = $itemsToPrint->map(function ($item) {
                return [
                    'name' => $item->menuItem->name,
                    'quantity' => $item->quantity,
                    'notes' => $item->item_notes,
                    'course' => $item->course,
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
                        'course' => $item->course,
                    ];
                })->toArray();
            }

            KitchenPrint::create([
                'order_id' => $order->id,
                'type' => $order->kitchenPrints()->count() == 0 ? 'new_order' : 'add_item',
                'print_content' => $printContent,
                'user_id' => Auth::id(),
            ]);

            $printer->printKitchenTicket($order, $printContent);

            foreach ($itemsToPrint as $item) {
                $item->printed_to_kitchen = true;
                $item->status = $item->status === 'hold' ? 'hold' : 'sent_to_kitchen';
                $item->save();
            }
            if ($request->input('force_reprint_all')) {
                $order->orderItems()->where('status', '!=', 'cancelled')->update([
                    'printed_to_kitchen' => true,
                    'status' => DB::raw("CASE WHEN status = 'hold' THEN 'hold' ELSE 'sent_to_kitchen' END")
                ]);
            }

            if ($order->status == 'pending') {
                $order->status = 'preparing';
                $order->save();
            }

            DB::commit();
            event(new OrderUpdated($order));

            session()->flash('kitchen_print_content', $printContent);
            session()->flash('kitchen_print_order_id', $order->id);
            session()->flash('kitchen_print_table_name', $order->restaurantTable->name);

            return back()->with('success', 'Order sent to kitchen!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to send order to kitchen. ' . $e->getMessage());
        }
    }
}
