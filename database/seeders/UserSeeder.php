<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'role' => 'admin',
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Legacy login kept as full admin (all permissions via isAdmin).
        User::firstOrCreate(
            ['email' => 'seller@gmail.com'],
            [
                'role' => 'admin',
                'name' => 'Seller',
                'password' => Hash::make('password'),
            ]
        );

        $staff = [
            ['name' => 'Cashier', 'email' => 'cashier@gmail.com', 'permissions' => ['dashboard', 'pos', 'sales']],
            ['name' => 'Manager', 'email' => 'manager@gmail.com', 'permissions' => ['dashboard', 'pos', 'products', 'stocks', 'sales', 'reports', 'reservations', 'floors']],
            ['name' => 'Chef', 'email' => 'chef@gmail.com', 'permissions' => ['kds']],
        ];

        foreach ($staff as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role' => 'employee',
                    'parent_id' => $admin->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'permissions' => $data['permissions'],
                ]
            );
        }
    }
}

