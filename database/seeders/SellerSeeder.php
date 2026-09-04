<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\SellerEmployee;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Floor staff per branch (manager + chef + waiters + cleaner).
 * Login users (admin/employees) live in UserSeeder; these are outlet staff records.
 */
class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $sellerId = User::admin()->orderBy('id')->first()->id;

        $branches = Branch::where('seller_id', $sellerId)->orderBy('id')->get();

        if ($branches->isEmpty()) {
            return;
        }

        $names = [
            SellerEmployee::MANAGER => ['Arif Hossain', 'Nasir Uddin'],
            SellerEmployee::CHEF => ['Karim Sheikh', 'Mizanur Rahman', 'Delowar Hossain'],
            SellerEmployee::WAITER => ['Rahim Uddin', 'Sakib Hasan', 'Fahim Ahmed', 'Tanvir Islam', 'Nayeem Ali', 'Sabbir Chowdhury'],
            SellerEmployee::CLEANER => ['Jamal Uddin', 'Rashed Khan'],
        ];

        $i = 0;
        foreach ($branches as $branch) {
            // 1 manager per branch.
            SellerEmployee::firstOrCreate(
                ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $names[SellerEmployee::MANAGER][$i % count($names[SellerEmployee::MANAGER])]." ({$branch->code})"],
                ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $names[SellerEmployee::MANAGER][$i % count($names[SellerEmployee::MANAGER])]." ({$branch->code})", 'role' => SellerEmployee::MANAGER]
            );

            // 1 chef per branch.
            SellerEmployee::firstOrCreate(
                ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $names[SellerEmployee::CHEF][$i % count($names[SellerEmployee::CHEF])]." ({$branch->code})"],
                ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $names[SellerEmployee::CHEF][$i % count($names[SellerEmployee::CHEF])]." ({$branch->code})", 'role' => SellerEmployee::CHEF]
            );

            // 3 waiters per branch.
            for ($w = 0; $w < 3; $w++) {
                $name = $names[SellerEmployee::WAITER][($i * 3 + $w) % count($names[SellerEmployee::WAITER])]." ({$branch->code}-W".($w + 1).')';
                SellerEmployee::firstOrCreate(
                    ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $name],
                    ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $name, 'role' => SellerEmployee::WAITER]
                );
            }

            // 1 cleaner per branch.
            SellerEmployee::firstOrCreate(
                ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $names[SellerEmployee::CLEANER][$i % count($names[SellerEmployee::CLEANER])]." ({$branch->code})"],
                ['seller_id' => $sellerId, 'branch_id' => $branch->id, 'name' => $names[SellerEmployee::CLEANER][$i % count($names[SellerEmployee::CLEANER])]." ({$branch->code})", 'role' => SellerEmployee::CLEANER]
            );

            $i++;
        }
    }
}
