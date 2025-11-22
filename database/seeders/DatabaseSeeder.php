<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Demo users for quick testing
        User::updateOrCreate(
            ['phone' => '01700000001'],
            [
                'name' => 'Driver Demo',
                'email' => 'driver@example.com',
                'role' => 'driver',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['phone' => '01700000002'],
            [
                'name' => 'Provider Demo',
                'email' => 'provider@example.com',
                'role' => 'provider',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['phone' => '01700000003'],
            [
                'name' => 'Admin Demo',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
