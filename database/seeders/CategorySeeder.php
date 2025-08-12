<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
         DB::table('categories')->delete();
        Category::create(['name' => 'Starters', 'description' => 'Appetizers to begin your meal.']);
        Category::create(['name' => 'Main Course', 'description' => 'Hearty main dishes.']);
        Category::create(['name' => 'Desserts', 'description' => 'Sweet treats to finish.']);
        Category::create(['name' => 'Beverages', 'description' => 'Drinks and refreshments.']);
    }
}