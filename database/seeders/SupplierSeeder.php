<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('suppliers')->delete(); // Clear existing data

        Supplier::create([
            'name' => 'Fresh Veggies Co.',
            'contact_person' => 'John Doe',
            'email' => 'john.doe@freshveggies.com',
            'phone' => '555-0101',
            'address' => '123 Green St, Farmville',
        ]);

        Supplier::create([
            'name' => 'Prime Meats Ltd.',
            'contact_person' => 'Jane Smith',
            'email' => 'jane.smith@primemeats.com',
            'phone' => '555-0202',
            'address' => '456 Butcher Ave, Meatpacking District',
        ]);

        Supplier::create([
            'name' => 'Global Dry Goods Inc.',
            'contact_person' => 'Sam Wilson',
            'email' => 'sam.wilson@globaldry.com',
            'phone' => '555-0303',
            'address' => '789 Warehouse Rd, Port City',
        ]);

        Supplier::create([
            'name' => 'Beverage King Distributors',
            'contact_person' => 'Alice Brown',
            'email' => 'alice.brown@bevking.com',
            'phone' => '555-0404',
            'address' => '101 Drink Ln, Thirsty Town',
        ]);
    }
}
