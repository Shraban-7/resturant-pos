<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Enums\EmployeeRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Floor staff per branch (manager + chef + waiters + cleaner).
 * Login users (admin/employees) live in UserSeeder; these are outlet staff records.
 */
class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::admin()->orderBy('id')->first()->id;

        $branches = Branch::where('admin_id', $ownerId)->orderBy('id')->get();

        if ($branches->isEmpty()) {
            return;
        }

        $names = [
            EmployeeRole::MANAGER->value => ['Arif Hossain', 'Nasir Uddin'],
            EmployeeRole::CHEF->value => ['Karim Sheikh', 'Mizanur Rahman', 'Delowar Hossain'],
            EmployeeRole::WAITER->value => ['Rahim Uddin', 'Sakib Hasan', 'Fahim Ahmed', 'Tanvir Islam', 'Nayeem Ali', 'Sabbir Chowdhury'],
            EmployeeRole::CLEANER->value => ['Jamal Uddin', 'Rashed Khan'],
        ];

        $i = 0;
        foreach ($branches as $branch) {
            // 1 manager per branch.
            Employee::firstOrCreate(
                ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $names[EmployeeRole::MANAGER->value][$i % count($names[EmployeeRole::MANAGER->value])]." ({$branch->code})"],
                ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $names[EmployeeRole::MANAGER->value][$i % count($names[EmployeeRole::MANAGER->value])]." ({$branch->code})", 'role' => EmployeeRole::MANAGER->value]
            );

            // 1 chef per branch.
            Employee::firstOrCreate(
                ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $names[EmployeeRole::CHEF->value][$i % count($names[EmployeeRole::CHEF->value])]." ({$branch->code})"],
                ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $names[EmployeeRole::CHEF->value][$i % count($names[EmployeeRole::CHEF->value])]." ({$branch->code})", 'role' => EmployeeRole::CHEF->value]
            );

            // 3 waiters per branch.
            for ($w = 0; $w < 3; $w++) {
                $name = $names[EmployeeRole::WAITER->value][($i * 3 + $w) % count($names[EmployeeRole::WAITER->value])]." ({$branch->code}-W".($w + 1).')';
                Employee::firstOrCreate(
                    ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $name],
                    ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $name, 'role' => EmployeeRole::WAITER->value]
                );
            }

            // 1 cleaner per branch.
            Employee::firstOrCreate(
                ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $names[EmployeeRole::CLEANER->value][$i % count($names[EmployeeRole::CLEANER->value])]." ({$branch->code})"],
                ['admin_id' => $ownerId, 'branch_id' => $branch->id, 'name' => $names[EmployeeRole::CLEANER->value][$i % count($names[EmployeeRole::CLEANER->value])]." ({$branch->code})", 'role' => EmployeeRole::CLEANER->value]
            );

            $i++;
        }
    }
}



