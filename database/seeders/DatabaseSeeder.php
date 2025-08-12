<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $this->call([
            UserSeeder::class,
            RestaurantTableSeeder::class,
            CategorySeeder::class,
            MenuItemSeeder::class,
        ]);
          $this->call(SupplierSeeder::class);
        $this->call(ExpenseCategorySeeder::class);

        // Data that might depend on the above (like inventory items)
        $this->call(InventoryItemSeeder::class);

        // Data that depends on Inventory Items (for initial stock)
        // and Users (for who recorded the transaction)
        $this->call(StockTransactionSeeder::class);

        // Data that depends on Expense Categories and Users
        $this->call(ExpenseSeeder::class);
    }
}
