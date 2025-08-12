<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);
        User::create([
            'name' => 'Reception User',
            'email' => 'reception@example.com',
            'password' => Hash::make('password'),
            'role' => 'reception',
        ]);
        User::create([
            'name' => 'Waiter User',
            'email' => 'waiter@example.com',
            'password' => Hash::make('password'),
            'role' => 'waiter',
        ]);
        User::factory(5)->create(['role' => 'waiter']); // More waiters
    }
}