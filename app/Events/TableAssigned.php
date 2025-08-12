<?php

namespace App\Events;

use App\Models\RestaurantTable;
use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class TableAssigned
{
    use Dispatchable, SerializesModels;

    public $table;
    public $order;

    public function __construct(RestaurantTable $table, Order $order = null)
    {
        $this->table = $table;
        $this->order = $order;

        // Send to Socket.io server
        $client = new Client();
        try {
            $client->post('http://localhost:3000/broadcast', [
                'json' => [
                    'event' => 'table_updated',
                    'data' => [
                        'table' => [
                            'id' => $this->table->id,
                            'name' => $this->table->name,
                            'status' => $this->table->status,
                            'current_order' => $this->order ? [
                                'id' => $this->order->id,
                                'waiter_name' => $this->order->waiter->name,
                                'customer_count' => $this->order->customer_count,
                                'status' => $this->order->status,
                            ] : null,
                        ],
                    ],
                    'roles' => ['waiter', 'reception', 'superadmin'],
                ],
            ]);
        } catch (\Exception $e) {
            // Log error (e.g., Log::error($e->getMessage()));
        }
    }
}