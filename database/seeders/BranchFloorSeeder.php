<?php

namespace Database\Seeders;

use App\Enums\TableStatus;

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

        // Branches come from BranchSeeder; here we only add floors + tables.
        $branches = Branch::where('admin_id', $ownerId)->orderBy('id')->get();

        foreach ($branches as $branch) {

            foreach (['Ground Floor' => 1, 'First Floor' => 2] as $floorName => $priority) {
                $floor = Floor::firstOrCreate(
                    ['admin_id' => $ownerId, 'name' => "{$branch->code} - {$floorName}"],
                    ['admin_id' => $ownerId, 'name' => "{$branch->code} - {$floorName}", 'priority' => $priority]
                );

                for ($i = 1; $i <= 5; $i++) {
                    DiningTable::firstOrCreate(
                        ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => "T-{$branch->code}-{$floorName[0]}{$i}"],
                        [
                            'admin_id' => $ownerId,
                            'branch_id' => $branch->id,
                            'floor_id' => $floor->id,
                            'name' => "T-{$branch->code}-{$floorName[0]}{$i}",
                            'status' => TableStatus::FREE,
                        ]
                    );
                }
            }
        }
    }
}




