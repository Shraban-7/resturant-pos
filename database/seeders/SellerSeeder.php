<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\DiningTable;
use App\Models\SellerEmployee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SellerSeeder extends Seeder
{
    public function run()
    {
        $sellerId = User::seller()->first()->id;

        $businessName = BusinessSetting::where('user_id', $sellerId)->first()->name ?? '';

        for ($i = 1; $i <= 10; $i++) {
            DiningTable::create([
                'name' => 'Table ' . $i,
                'status' => DiningTable::FREE,
                'seller_id' => $sellerId,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            SellerEmployee::create([
                'name' => "{$businessName} Waiter {$i}",
                'seller_id' => $sellerId,
                'role' => SellerEmployee::WAITER,
            ]);
        }
    }
}
