<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class OrderUpdated
{
    use Dispatchable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;

        // Send to Socket.io server
        $client = new Client();
        try {
            $client->post('http://localhost:3000/broadcast', [
                'json' => [
                    'event' => 'order_updated',
                    'data' => [
                        'order' => [
                            'id' => $this->order->id,
                            'table_id' => $this->order->restaurant_table_id,
                            'table_name' => $this->order->restaurantTable->name,
                            'waiter_name' => $this->order->waiter->name,
                            'customer_count' => $this->order->customer_count,
                            'status' => $this->order->status,
                            'total_amount' => $this->order->total_amount,
                            'final_total' => $this->order->final_total,
                            'updated_at' => $this->order->updated_at->toDateTimeString(),
                            'order_items' => $this->order->orderItems->map(function ($item) {
                                return [
                                    'id' => $item->id,
                                    'menu_item_name' => $item->menuItem->name,
                                    'quantity' => $item->quantity,
                                    'price_at_order' => $item->price_at_order,
                                    'item_notes' => $item->item_notes,
                                    'status' => $item->status,
                                    'printed_to_kitchen' => $item->printed_to_kitchen,
                                    'created_at' => $item->created_at?->toDateTimeString(),
                                ];
                            })->toArray(),
                        ],
                    ],
                    'roles' => ['waiter', 'reception', 'superadmin'],
                ],
            ]);
        } catch (\Exception $e) {
            // Log error
        }
    }
}