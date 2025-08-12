<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\Category;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $starters = Category::where('name', 'Starters')->first();
        $main = Category::where('name', 'Main Course')->first();
        // ... get other categories

        if ($starters) {
            MenuItem::create(['category_id' => $starters->id, 'name' => 'Spring Rolls', 'price' => 8.50]);
            MenuItem::create(['category_id' => $starters->id, 'name' => 'Garlic Bread', 'price' => 6.00]);
        }
        if ($main) {
            MenuItem::create(['category_id' => $main->id, 'name' => 'Steak Frites', 'price' => 25.00]);
            MenuItem::create(['category_id' => $main->id, 'name' => 'Pasta Carbonara', 'price' => 18.00]);
        }
        // Add more items...
    }
}