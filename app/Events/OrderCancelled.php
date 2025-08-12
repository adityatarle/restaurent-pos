<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;

        // Store notification for reception users
        $receptionUsers = User::where('role', 'reception')->get();
        foreach ($receptionUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'message' => "Order #{$order->id} at Table {$order->restaurantTable->name} has been cancelled.",
                'link' => route('reception.bill.generate', $order->id),
                'is_read' => false,
            ]);
        }
    }

    public function broadcastOn(): Channel
    {
        return new Channel('reception-notifications');
    }

    public function broadcastWith(): array
    {
        return [
            'message' => "Order #{$this->order->id} at Table {$this->order->restaurantTable->name} has been cancelled.",
            'link' => route('reception.bill.generate', $this->order->id),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}