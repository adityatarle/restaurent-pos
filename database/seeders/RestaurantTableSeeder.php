<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\RestaurantTable;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            RestaurantTable::create([
                'name' => 'Table ' . $i,
                'capacity' => rand(2, 6),
            ]);
        }
        RestaurantTable::create(['name' => 'Patio A1', 'capacity' => 4]);
        RestaurantTable::create(['name' => 'Bar Seat 1', 'capacity' => 1]);
    }
}