<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '1234567890',
                'address' => '123 Admin Street',
                'age' => 30,
                'role' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10)
            ]);
        }

        // Create sample drivers
        $drivers = [
            [
                'name' => 'John Driver',
                'email' => 'driver1@example.com',
                'phone' => '1234567891',
                'address' => '456 Driver Street',
                'age' => 35,
                'role' => 'driver',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10)
            ],
            [
                'name' => 'Sarah Driver',
                'email' => 'driver2@example.com',
                'phone' => '1234567892',
                'address' => '789 Driver Street',
                'age' => 32,
                'role' => 'driver',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10)
            ]
        ];

        foreach ($drivers as $driverData) {
            User::create($driverData);
        }
    }
}
