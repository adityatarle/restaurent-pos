<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PrinterService
{
    public function printKitchenTicket(Order $order, array $itemsToPrint): void
    {
        $order->loadMissing('restaurantTable');
        $lines = [];
        $lines[] = 'KITCHEN TICKET';
        $lines[] = 'Order: #' . $order->id;
        $lines[] = 'Table: ' . ($order->restaurantTable?->name ?? ('#' . $order->restaurant_table_id));
        $lines[] = 'Customer Count: ' . $order->customer_count;
        $lines[] = '-----------------------------';
        foreach ($itemsToPrint as $item) {
            $name = $item['name'] ?? 'Item';
            $qty = $item['quantity'] ?? 1;
            $notes = trim((string)($item['notes'] ?? ''));
            $lines[] = sprintf('%-22s x%2d', $name, $qty) . ($notes ? ' | ' . $notes : '');
        }
        $lines[] = '-----------------------------';
        $lines[] = 'Printed: ' . Carbon::now()->toDateTimeString();

        $content = implode("\n", $lines) . "\n";
        $dir = 'prints/kitchen';
        $filename = sprintf('order_%d_%s.txt', $order->id, Carbon::now()->format('Ymd_His'));
        Storage::disk('local')->put($dir . '/' . $filename, $content);
    }

    public function printKitchenCancellation(Order $order, array $itemsCancelled): void
    {
        $order->loadMissing('restaurantTable');
        $lines = [];
        $lines[] = 'KITCHEN CANCEL';
        $lines[] = 'Order: #' . $order->id;
        $lines[] = 'Table: ' . ($order->restaurantTable?->name ?? ('#' . $order->restaurant_table_id));
        $lines[] = '-----------------------------';
        foreach ($itemsCancelled as $item) {
            $name = $item['name'] ?? 'Item';
            $qty = $item['quantity'] ?? 0;
            $lines[] = sprintf('CANCEL %-20s x%2d', $name, $qty);
        }
        $lines[] = '-----------------------------';
        $lines[] = 'Printed: ' . Carbon::now()->toDateTimeString();

        $content = implode("\n", $lines) . "\n";
        $dir = 'prints/kitchen';
        $filename = sprintf('order_%d_cancel_%s.txt', $order->id, Carbon::now()->format('Ymd_His'));
        Storage::disk('local')->put($dir . '/' . $filename, $content);
    }
}