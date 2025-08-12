<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('inventory_items')->delete(); // Clear existing data

        // Produce
        InventoryItem::create([
            'name' => 'Tomatoes',
            'description' => 'Fresh, ripe tomatoes',
            'category' => 'Produce',
            'unit_of_measure' => 'kg',
            'current_stock' => 0, // Initial stock will be added via StockTransactionSeeder
            'reorder_level' => 5,
            'average_cost_price' => 2.50,
        ]);
        InventoryItem::create([
            'name' => 'Onions',
            'description' => 'Yellow onions',
            'category' => 'Produce',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 10,
            'average_cost_price' => 1.80,
        ]);
        InventoryItem::create([
            'name' => 'Potatoes',
            'description' => 'Russet potatoes',
            'category' => 'Produce',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 15,
            'average_cost_price' => 1.50,
        ]);

        // Meats
        InventoryItem::create([
            'name' => 'Chicken Breast',
            'description' => 'Boneless, skinless chicken breast',
            'category' => 'Meat',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 8,
            'average_cost_price' => 8.00,
        ]);
        InventoryItem::create([
            'name' => 'Beef Steak (Sirloin)',
            'description' => 'Sirloin cut',
            'category' => 'Meat',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 4,
            'average_cost_price' => 15.50,
        ]);

        // Dairy
        InventoryItem::create([
            'name' => 'Milk',
            'description' => 'Whole milk',
            'category' => 'Dairy',
            'unit_of_measure' => 'ltr',
            'current_stock' => 0,
            'reorder_level' => 10,
            'average_cost_price' => 1.20,
        ]);
        InventoryItem::create([
            'name' => 'Cheese (Cheddar)',
            'description' => 'Block of cheddar cheese',
            'category' => 'Dairy',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 3,
            'average_cost_price' => 9.50,
        ]);

        // Dry Goods
        InventoryItem::create([
            'name' => 'Flour (All-Purpose)',
            'description' => 'Standard all-purpose flour',
            'category' => 'Dry Goods',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 20,
            'average_cost_price' => 1.00,
        ]);
        InventoryItem::create([
            'name' => 'Pasta (Spaghetti)',
            'description' => 'Dry spaghetti pasta',
            'category' => 'Dry Goods',
            'unit_of_measure' => 'kg',
            'current_stock' => 0,
            'reorder_level' => 10,
            'average_cost_price' => 2.20,
        ]);

        // Beverages
        InventoryItem::create([
            'name' => 'Cola Cans',
            'description' => 'Standard 330ml cola cans',
            'category' => 'Beverages',
            'unit_of_measure' => 'case (24 pcs)', // Or 'pcs' and manage packs
            'current_stock' => 0,
            'reorder_level' => 5, // 5 cases
            'average_cost_price' => 12.00, // Per case
        ]);
         InventoryItem::create([
            'name' => 'Mineral Water Bottle (500ml)',
            'description' => 'Bottled mineral water',
            'category' => 'Beverages',
            'unit_of_measure' => 'pcs',
            'current_stock' => 0,
            'reorder_level' => 50,
            'average_cost_price' => 0.30,
        ]);
    }
}