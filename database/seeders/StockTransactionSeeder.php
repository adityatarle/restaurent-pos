<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User; // Assuming you have a User model and want to associate transactions
use Illuminate\Support\Facades\DB;

class StockTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('stock_transactions')->delete(); // Clear existing data

        $items = InventoryItem::all();
        $adminUser = User::where('role', 'superadmin')->first() ?? User::first(); // Get an admin or any user
        $defaultSupplier = Supplier::first(); // Get a default supplier

        if (!$adminUser) {
            $this->command->info('No SuperAdmin user found for stock transactions. Please create one.');
            return;
        }

        foreach ($items as $item) {
            $initialStockQuantity = 0;
            $cost = $item->average_cost_price ?? 0; // Use item's avg cost or 0

            // Assign some initial stock based on item name or category as an example
            switch ($item->name) {
                case 'Tomatoes':
                    $initialStockQuantity = 10; // 10 kg
                    break;
                case 'Onions':
                    $initialStockQuantity = 15; // 15 kg
                    break;
                case 'Potatoes':
                    $initialStockQuantity = 25; // 25 kg
                    break;
                case 'Chicken Breast':
                    $initialStockQuantity = 12; // 12 kg
                    break;
                case 'Cola Cans':
                    $initialStockQuantity = 10; // 10 cases
                    break;
                case 'Flour (All-Purpose)':
                    $initialStockQuantity = 30; // 30 kg
                    break;
                default:
                    $initialStockQuantity = 5; // Default initial stock for others
            }

            if ($initialStockQuantity > 0) {
                StockTransaction::create([
                    'inventory_item_id' => $item->id,
                    'supplier_id' => $defaultSupplier ? $defaultSupplier->id : null, // Optional
                    'type' => 'opening_stock', // Or 'purchase' if you prefer
                    'quantity' => $initialStockQuantity,
                    'cost_price_at_transaction' => $cost,
                    'notes' => 'Initial stock seeding',
                    'transaction_date' => now()->subDay(), // A bit in the past
                    'user_id' => $adminUser->id,
                ]);

                // Update the current_stock in inventory_items table
                $item->update(['current_stock' => $initialStockQuantity]);
            }
        }
    }
}