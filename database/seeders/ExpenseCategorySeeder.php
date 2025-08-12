<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expense_categories')->delete(); // Clear existing data

        $categories = [
            ['name' => 'Rent', 'description' => 'Monthly rent for the premises.'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, gas, internet bills.'],
            ['name' => 'Salaries & Wages', 'description' => 'Staff salaries and wages.'],
            ['name' => 'Food Supplies Purchase', 'description' => 'Cost of purchasing raw food ingredients.'],
            ['name' => 'Beverage Supplies Purchase', 'description' => 'Cost of purchasing beverages.'],
            ['name' => 'Marketing & Advertising', 'description' => 'Promotional expenses.'],
            ['name' => 'Maintenance & Repairs', 'description' => 'Equipment repairs, general maintenance.'],
            ['name' => 'Cleaning Supplies', 'description' => 'Detergents, cleaning tools, etc.'],
            ['name' => 'Packaging Supplies', 'description' => 'Takeaway containers, bags, etc.'],
            ['name' => 'Licenses & Permits', 'description' => 'Business licenses, health permits.'],
            ['name' => 'Bank Charges', 'description' => 'Fees related to banking services.'],
            ['name' => 'Miscellaneous', 'description' => 'Other sundry expenses.'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}