<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\DiningTable;
use App\Models\Floor;
use App\Models\User;
use Illuminate\Database\Seeder;

class BranchFloorSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::admin()->first()->id;

        $branches = [
            ['name' => 'Head Office / HQ', 'code' => 'HQ', 'address' => 'House 12, Road 5, Gulshan, Dhaka', 'phone' => '01711111111', 'is_default' => true, 'is_active' => true],
            ['name' => 'Dhanmondi Branch', 'code' => 'DHN', 'address' => 'House 8, Road 27, Dhanmondi, Dhaka', 'phone' => '01722222222', 'is_default' => false, 'is_active' => true],
            ['name' => 'Uttara Branch', 'code' => 'UTR', 'address' => 'Plot 4, Sector 11, Uttara, Dhaka', 'phone' => '01733333333', 'is_default' => false, 'is_active' => true],
        ];

        foreach ($branches as $data) {
            $branch = Branch::firstOrCreate(
                ['seller_id' => $ownerId, 'code' => $data['code']],
                $data + ['seller_id' => $ownerId]
            );

            foreach (['Ground Floor' => 1, 'First Floor' => 2] as $floorName => $priority) {
                $floor = Floor::firstOrCreate(
                    ['seller_id' => $ownerId, 'name' => "{$branch->code} - {$floorName}"],
                    ['seller_id' => $ownerId, 'name' => "{$branch->code} - {$floorName}", 'priority' => $priority]
                );

                for ($i = 1; $i <= 5; $i++) {
                    DiningTable::firstOrCreate(
                        ['seller_id' => $ownerId, 'branch_id' => $branch->id, 'name' => "T-{$branch->code}-{$floorName[0]}{$i}"],
                        [
                            'seller_id' => $ownerId,
                            'branch_id' => $branch->id,
                            'floor_id' => $floor->id,
                            'name' => "T-{$branch->code}-{$floorName[0]}{$i}",
                            'status' => DiningTable::FREE,
                        ]
                    );
                }
            }
        }
    }
}
