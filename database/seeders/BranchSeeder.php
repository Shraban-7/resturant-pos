<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Realistic Dhaka restaurant branches.
 * HQ (Gulshan) is the default branch; others are full outlets.
 */
class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::admin()->orderBy('id')->first()->id;

        $branches = [
            [
                'name' => 'Gulshan Flagship (HQ)', 'code' => 'HQ',
                'address' => 'House 12, Road 5, Gulshan Avenue, Dhaka 1212',
                'phone' => '01711111111', 'is_default' => true, 'is_active' => true,
            ],
            [
                'name' => 'Dhanmondi Outlet', 'code' => 'DHN',
                'address' => 'House 8, Road 27, Dhanmondi, Dhaka 1205',
                'phone' => '01722222222', 'is_default' => false, 'is_active' => true,
            ],
            [
                'name' => 'Uttara Outlet', 'code' => 'UTR',
                'address' => 'Plot 4, Sector 11, Sonargaon Janapath, Uttara, Dhaka 1230',
                'phone' => '01733333333', 'is_default' => false, 'is_active' => true,
            ],
            [
                'name' => 'Mirpur Outlet', 'code' => 'MRP',
                'address' => 'Plot 2, Road 7, Mirpur 10, Dhaka 1216',
                'phone' => '01744444444', 'is_default' => false, 'is_active' => true,
            ],
            [
                'name' => 'Banani Outlet', 'code' => 'BNN',
                'address' => 'House 25, Road 11, Banani, Dhaka 1213',
                'phone' => '01755555555', 'is_default' => false, 'is_active' => true,
            ],
        ];

        foreach ($branches as $data) {
            Branch::firstOrCreate(
                ['seller_id' => $ownerId, 'code' => $data['code']],
                $data + ['seller_id' => $ownerId]
            );
        }
    }
}
