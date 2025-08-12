<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User; // Assuming you have users
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expenses')->delete(); // Clear existing data

        $adminUser = User::where('role', 'superadmin')->first() ?? User::first();
        if (!$adminUser) {
            $this->command->info('No SuperAdmin user found for expenses. Please create one.');
            return;
        }

        $rentCategory = ExpenseCategory::where('name', 'Rent')->first();
        $utilitiesCategory = ExpenseCategory::where('name', 'Utilities')->first();
        $foodSuppliesCategory = ExpenseCategory::where('name', 'Food Supplies Purchase')->first();
        $salariesCategory = ExpenseCategory::where('name', 'Salaries & Wages')->first();

        // Last month's expenses
        $lastMonth = Carbon::now()->subMonth();

        if ($rentCategory) {
            Expense::create([
                'expense_category_id' => $rentCategory->id,
                'description' => 'Monthly Rent - ' . $lastMonth->format('F Y'),
                'amount' => 2500.00,
                'expense_date' => $lastMonth->startOfMonth()->addDays(rand(0,4))->toDateString(),
                'vendor_name' => 'City Properties Ltd.',
                'user_id' => $adminUser->id,
            ]);
        }

        if ($utilitiesCategory) {
            Expense::create([
                'expense_category_id' => $utilitiesCategory->id,
                'description' => 'Electricity Bill - ' . $lastMonth->format('F Y'),
                'amount' => 350.75,
                'expense_date' => $lastMonth->startOfMonth()->addDays(rand(5,10))->toDateString(),
                'vendor_name' => 'Power Grid Corp',
                'user_id' => $adminUser->id,
            ]);
            Expense::create([
                'expense_category_id' => $utilitiesCategory->id,
                'description' => 'Water Bill - ' . $lastMonth->format('F Y'),
                'amount' => 120.50,
                'expense_date' => $lastMonth->startOfMonth()->addDays(rand(5,10))->toDateString(),
                'vendor_name' => 'City Water Dept',
                'user_id' => $adminUser->id,
            ]);
        }

        if ($foodSuppliesCategory) {
            Expense::create([
                'expense_category_id' => $foodSuppliesCategory->id,
                'description' => 'Vegetable Purchase from Fresh Veggies Co. - Week 1 ' . $lastMonth->format('F'),
                'amount' => 450.00,
                'expense_date' => $lastMonth->startOfMonth()->addDays(rand(1,6))->toDateString(),
                'vendor_name' => 'Fresh Veggies Co.',
                'user_id' => $adminUser->id,
            ]);
            Expense::create([
                'expense_category_id' => $foodSuppliesCategory->id,
                'description' => 'Meat Purchase from Prime Meats Ltd. - Week 2 ' . $lastMonth->format('F'),
                'amount' => 675.00,
                'expense_date' => $lastMonth->startOfMonth()->addDays(rand(7,13))->toDateString(),
                'vendor_name' => 'Prime Meats Ltd.',
                'user_id' => $adminUser->id,
            ]);
        }

        if ($salariesCategory) {
            Expense::create([
                'expense_category_id' => $salariesCategory->id,
                'description' => 'Staff Salaries - ' . $lastMonth->format('F Y'),
                'amount' => 5500.00,
                'expense_date' => $lastMonth->endOfMonth()->toDateString(),
                'user_id' => $adminUser->id,
            ]);
        }

        // Current month's expenses (few examples)
        $currentMonth = Carbon::now();
         if ($rentCategory) {
            Expense::create([
                'expense_category_id' => $rentCategory->id,
                'description' => 'Monthly Rent - ' . $currentMonth->format('F Y'),
                'amount' => 2500.00,
                'expense_date' => $currentMonth->startOfMonth()->addDays(rand(0,4))->toDateString(),
                'vendor_name' => 'City Properties Ltd.',
                'user_id' => $adminUser->id,
            ]);
        }
         if ($foodSuppliesCategory) {
            Expense::create([
                'expense_category_id' => $foodSuppliesCategory->id,
                'description' => 'Dry Goods from Global Dry Goods - ' . $currentMonth->format('F'),
                'amount' => 320.00,
                'expense_date' => $currentMonth->startOfMonth()->addDays(rand(1,3))->toDateString(),
                'vendor_name' => 'Global Dry Goods Inc.',
                'user_id' => $adminUser->id,
            ]);
        }
    }
}